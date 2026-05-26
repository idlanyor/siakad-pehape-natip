<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PembayaranSpp;
use App\Models\TagihanSpp;
use Illuminate\Http\Request;

class LaporanKeuanganController extends Controller
{
    public function index(Request $request)
    {
        // Mode filter: per bulan, per semester (6 bulan), atau per tahun (12 bulan)
        $mode = $request->mode ?? 'bulan';

        if ($mode === 'tahun' && $request->filled('tahun')) {
            $tahunVal = (int) $request->tahun;
            $query = TagihanSpp::with('siswa.kelas')
                ->whereYear('bulan', $tahunVal);
            $filterLabel = "Tahun {$tahunVal}";
        } elseif ($mode === 'range' && $request->filled('bulan_mulai') && $request->filled('bulan_selesai')) {
            $query = TagihanSpp::with('siswa.kelas')
                ->where('bulan', '>=', $request->bulan_mulai . '-01')
                ->where('bulan', '<=', $request->bulan_selesai . '-28');
            $filterLabel = $request->bulan_mulai . ' s.d. ' . $request->bulan_selesai;
        } else {
            // Default: per bulan
            $mode = 'bulan';
            $bulanFilter = $request->bulan ?? now()->format('Y-m');
            $tahun = (int) substr($bulanFilter, 0, 4);
            $bulan = (int) substr($bulanFilter, 5, 2);
            $query = TagihanSpp::with('siswa.kelas')
                ->whereYear('bulan', $tahun)
                ->whereMonth('bulan', $bulan);
            $filterLabel = \Carbon\Carbon::createFromFormat('Y-m', $bulanFilter)->isoFormat('MMMM YYYY');
        }

        $tagihans = $query->get();

        // Summary global
        $terkumpul = PembayaranSpp::whereIn('tagihan_spp_id', $tagihans->pluck('id'))->sum('nominal_bayar');
        $summary = [
            'total_tagihan'   => $tagihans->count(),
            'total_nominal'   => $tagihans->sum('nominal'),
            'jumlah_lunas'    => $tagihans->where('status', 'lunas')->count(),
            'jumlah_cicil'    => $tagihans->where('status', 'cicil')->count(),
            'jumlah_belum'    => $tagihans->where('status', 'belum')->count(),
            'total_terkumpul' => (float) $terkumpul,
        ];
        $summary['total_tunggakan'] = $summary['total_nominal'] - $summary['total_terkumpul'];

        // Rekap per kelas
        $allKelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $rekapKelas = $allKelas->map(function (Kelas $k) use ($tagihans) {
            $tagihanKelas = $tagihans->filter(fn($t) => $t->siswa?->kelas_id === $k->id);
            if ($tagihanKelas->isEmpty()) return null;

            return [
                'kelas'        => $k,
                'total'        => $tagihanKelas->count(),
                'lunas'        => $tagihanKelas->where('status', 'lunas')->count(),
                'cicil'        => $tagihanKelas->where('status', 'cicil')->count(),
                'belum'        => $tagihanKelas->where('status', 'belum')->count(),
                'persen_lunas' => $tagihanKelas->count() > 0
                    ? round(($tagihanKelas->where('status', 'lunas')->count() / $tagihanKelas->count()) * 100)
                    : 0,
            ];
        })->filter()->values();

        $bulanFilter = $request->bulan ?? now()->format('Y-m');

        return view('admin.keuangan.laporan.index', compact('summary', 'rekapKelas', 'bulanFilter', 'mode', 'filterLabel'));
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
            ->orderBy('status');

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

    public function kwitansi(PembayaranSpp $pembayaran)
    {
        $pembayaran->load(['tagihan.siswa.kelas.jurusan', 'pencatat']);
        return view('admin.keuangan.kwitansi', compact('pembayaran'));
    }
}
