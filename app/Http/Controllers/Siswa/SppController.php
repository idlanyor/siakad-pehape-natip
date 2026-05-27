<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PembayaranSpp;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class SppController extends Controller
{
    private function getSiswa(): Siswa
    {
        return Siswa::where('user_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $siswa = $this->getSiswa();

        $tagihans = TagihanSpp::where('siswa_id', $siswa->id)
            ->orderBy('bulan', 'desc')
            ->get();

        // Ringkasan
        $summary = [
            'total_tagihan'   => $tagihans->count(),
            'jumlah_lunas'    => $tagihans->where('status', 'lunas')->count(),
            'jumlah_belum'    => $tagihans->whereIn('status', ['belum', 'cicil'])->count(),
            'total_nominal'   => $tagihans->sum('nominal'),
            'total_terkumpul' => $tagihans->filter(fn($t) => $t->status !== 'belum')
                ->sum(fn($t) => (float) $t->pembayarans()->sum('nominal_bayar')),
        ];
        $summary['total_tunggakan'] = max(0, $summary['total_nominal'] - $summary['total_terkumpul']);

        return view('siswa.spp.index', compact('siswa', 'tagihans', 'summary'));
    }

    public function show(TagihanSpp $tagihan)
    {
        $siswa = $this->getSiswa();
        abort_if($tagihan->siswa_id !== $siswa->id, 403, 'Akses ditolak.');
        $tagihan->load('pembayarans.pencatat');

        return view('siswa.spp.show', compact('tagihan', 'siswa'));
    }

    /**
     * Generate Midtrans Snap token dan kirim ke view.
     * Dipanggil via POST dari tombol "Bayar Online".
     */
    public function getSnapToken(TagihanSpp $tagihan, MidtransService $midtrans)
    {
        $siswa = $this->getSiswa();
        abort_if($tagihan->siswa_id !== $siswa->id, 403, 'Akses ditolak.');
        abort_if($tagihan->status === 'lunas', 422, 'Tagihan sudah lunas.');
        abort_if($tagihan->sisa_tagihan <= 0, 422, 'Tidak ada sisa tagihan.');

        try {
            $result = $midtrans->createSnapToken($tagihan);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghubungi Midtrans: ' . $e->getMessage()], 500);
        }
    }

    public function uploadBukti(Request $request, TagihanSpp $tagihan)
    {
        $siswa = $this->getSiswa();
        abort_if($tagihan->siswa_id !== $siswa->id, 403, 'Akses ditolak.');
        abort_if($tagihan->status === 'lunas', 422, 'Tagihan sudah lunas.');

        $request->validate([
            'nominal_bayar' => 'required|numeric|min:1000|max:' . $tagihan->sisa_tagihan,
            'metode'        => 'required|in:transfer,qris',
            'bukti'         => 'required|image|max:2048',
            'catatan'       => 'nullable|string|max:255',
        ]);

        $buktiPath = $request->file('bukti')->store('spp/bukti', 'public');

        PembayaranSpp::create([
            'tagihan_spp_id'   => $tagihan->id,
            'tanggal_bayar'    => now()->toDateString(),
            'nominal_bayar'    => $request->nominal_bayar,
            'metode'           => $request->metode,
            'bukti'            => $buktiPath,
            'catatan'          => $request->catatan,
            'dicatat_oleh'     => auth()->id(),
            'status_konfirmasi'=> 'pending', // menunggu konfirmasi admin
        ]);

        return back()->with('success', 'Bukti transfer berhasil dikirim. Menunggu konfirmasi dari petugas TU.');
    }
}
