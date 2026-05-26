<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\TagihanSpp;

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

        // Pastikan tagihan ini milik siswa yang login
        abort_if($tagihan->siswa_id !== $siswa->id, 403, 'Akses ditolak.');

        $tagihan->load('pembayarans.pencatat');

        return view('siswa.spp.show', compact('tagihan', 'siswa'));
    }
}
