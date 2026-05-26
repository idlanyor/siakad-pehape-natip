<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TagihanSpp;
use Illuminate\Http\Request;

class LaporanKeuanganController extends Controller
{
    public function index(Request $request)
    {
        // Default: bulan ini
        $bulanFilter = $request->bulan ?? now()->format('Y-m');
        $tahun       = (int) substr($bulanFilter, 0, 4);
        $bulan       = (int) substr($bulanFilter, 5, 2);

        // Semua tagihan pada bulan tersebut
        $tagihans = TagihanSpp::with('siswa.kelas')
            ->whereYear('bulan', $tahun)
            ->whereMonth('bulan', $bulan)
            ->get();

        // Summary global
        $summary = [
            'total_tagihan'   => $tagihans->count(),
            'total_nominal'   => $tagihans->sum('nominal'),
            'jumlah_lunas'    => $tagihans->where('status', 'lunas')->count(),
            'jumlah_cicil'    => $tagihans->where('status', 'cicil')->count(),
            'jumlah_belum'    => $tagihans->where('status', 'belum')->count(),
            'total_terkumpul' => $tagihans->filter(fn($t) => $t->status !== 'belum')
                ->sum(fn($t) => (float) $t->pembayarans()->sum('nominal_bayar')),
        ];
        $summary['total_tunggakan'] = $summary['total_nominal'] - $summary['total_terkumpul'];

        // Rekap per kelas
        $allKelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $rekapKelas = $allKelas->map(function (Kelas $k) use ($tagihans) {
            $tagihanKelas = $tagihans->filter(fn($t) => $t->siswa?->kelas_id === $k->id);
            if ($tagihanKelas->isEmpty()) return null;

            return [
                'kelas'          => $k,
                'total'          => $tagihanKelas->count(),
                'lunas'          => $tagihanKelas->where('status', 'lunas')->count(),
                'cicil'          => $tagihanKelas->where('status', 'cicil')->count(),
                'belum'          => $tagihanKelas->where('status', 'belum')->count(),
                'persen_lunas'   => $tagihanKelas->count() > 0
                    ? round(($tagihanKelas->where('status', 'lunas')->count() / $tagihanKelas->count()) * 100)
                    : 0,
            ];
        })->filter()->values();

        return view('admin.keuangan.laporan.index', compact('summary', 'rekapKelas', 'bulanFilter'));
    }

    public function tunggakan(Request $request)
    {
        $bulanFilter = $request->bulan ?? now()->format('Y-m');
        $tahun       = (int) substr($bulanFilter, 0, 4);
        $bulan       = (int) substr($bulanFilter, 5, 2);

        $query = TagihanSpp::with(['siswa.kelas.jurusan', 'siswa.jurusan'])
            ->whereYear('bulan', $tahun)
            ->whereMonth('bulan', $bulan)
            ->where('status', '!=', 'lunas')
            ->orderBy('status') // belum dulu, cicil kedua
            ->orderByHas('siswa', fn($q) => $q->orderBy('nama'));

        if ($request->filled('kelas_id')) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $request->kelas_id));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tagihans   = $query->get();
        $kelas      = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $statusList = ['belum' => 'Belum Bayar', 'cicil' => 'Cicilan'];

        return view('admin.keuangan.laporan.tunggakan', compact('tagihans', 'kelas', 'statusList', 'bulanFilter'));
    }
}
