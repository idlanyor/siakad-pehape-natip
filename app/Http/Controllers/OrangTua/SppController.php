<?php

namespace App\Http\Controllers\OrangTua;

use App\Models\TagihanSpp;
use App\Services\MidtransService;

class SppController extends BaseOrangTuaController
{
    public function index()
    {
        $ortu = $this->orangTua();
        $anak = $this->anakAktif($ortu);
        abort_if(! $anak, 404, 'Belum ada data anak.');

        $tagihans = TagihanSpp::where('siswa_id', $anak->id)
            ->orderBy('bulan', 'desc')
            ->get();

        $summary = [
            'total_tagihan'   => $tagihans->count(),
            'jumlah_lunas'    => $tagihans->where('status', 'lunas')->count(),
            'jumlah_belum'    => $tagihans->whereIn('status', ['belum', 'cicil'])->count(),
            'total_nominal'   => $tagihans->sum('nominal'),
            'total_terkumpul' => $tagihans->filter(fn($t) => $t->status !== 'belum')
                ->sum(fn($t) => (float) $t->pembayarans()->sum('nominal_bayar')),
        ];
        $summary['total_tunggakan'] = max(0, $summary['total_nominal'] - $summary['total_terkumpul']);

        return view('orangtua.spp', compact('ortu', 'anak', 'tagihans', 'summary'));
    }

    public function show(TagihanSpp $tagihan)
    {
        $ortu = $this->orangTua();
        $this->pastikanMilikAnak($ortu, $tagihan);
        $tagihan->load('pembayarans.pencatat');

        $anak = $tagihan->siswa;

        return view('orangtua.spp-show', compact('tagihan', 'ortu', 'anak'));
    }

    /**
     * Generate Midtrans Snap token untuk tagihan anak (reuse Fase A).
     */
    public function getSnapToken(TagihanSpp $tagihan, MidtransService $midtrans)
    {
        $ortu = $this->orangTua();
        $this->pastikanMilikAnak($ortu, $tagihan);
        abort_if($tagihan->status === 'lunas', 422, 'Tagihan sudah lunas.');
        abort_if($tagihan->sisa_tagihan <= 0, 422, 'Tidak ada sisa tagihan.');

        try {
            $result = $midtrans->createSnapToken($tagihan);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghubungi Midtrans: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Pastikan tagihan milik salah satu anak dari orang tua ini (cegah IDOR).
     */
    protected function pastikanMilikAnak($ortu, TagihanSpp $tagihan): void
    {
        abort_unless($ortu->siswas->pluck('id')->contains($tagihan->siswa_id), 403, 'Akses ditolak.');
    }
}
