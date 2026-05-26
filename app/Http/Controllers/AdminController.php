<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PendaftarPpdb;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\TahunAjaran;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_siswa'    => Siswa::count(),
            'total_guru'     => Guru::count(),
            'total_kelas'    => Kelas::count(),
            'total_jurusan'  => Jurusan::count(),
            'total_mapel'    => Mapel::count(),
            'tahun_ajaran'   => TahunAjaran::aktif()?->nama ?? '-',
            'semester_aktif' => Semester::aktif()?->semester ?? '-',
            'ppdb_pending'   => PendaftarPpdb::whereIn('status', ['pending', 'verifikasi'])->count(),
            'spp_tunggakan'  => TagihanSpp::where('status', '!=', 'lunas')->count(),
        ];

        // Chart: Absensi 6 bulan terakhir
        $absensiChart = collect(range(5, 0))->map(function ($i) {
            $bulan = now()->subMonths($i);
            $query = Absensi::whereYear('tanggal', $bulan->year)->whereMonth('tanggal', $bulan->month);
            return [
                'label'  => $bulan->translatedFormat('M Y'),
                'hadir'  => (clone $query)->where('status', 'Hadir')->count(),
                'alpa'   => (clone $query)->where('status', 'Alpa')->count(),
                'izin'   => (clone $query)->where('status', 'Izin')->count(),
                'sakit'  => (clone $query)->where('status', 'Sakit')->count(),
            ];
        });

        // Chart: SPP status bulan ini
        $sppChart = [
            'lunas' => TagihanSpp::whereYear('bulan', now()->year)->whereMonth('bulan', now()->month)->where('status', 'lunas')->count(),
            'cicil' => TagihanSpp::whereYear('bulan', now()->year)->whereMonth('bulan', now()->month)->where('status', 'cicil')->count(),
            'belum' => TagihanSpp::whereYear('bulan', now()->year)->whereMonth('bulan', now()->month)->where('status', 'belum')->count(),
        ];

        // Chart: PPDB per jurusan
        $ppdbChart = Jurusan::withCount(['siswas as pendaftar_count' => function ($q) {
            $q->join('pendaftar_ppdbs', 'siswas.jurusan_id', '=', 'pendaftar_ppdbs.jurusan_pilihan_id');
        }])->get()->map(fn($j) => [
            'jurusan'  => $j->nama_jurusan,
            'diterima' => PendaftarPpdb::where('jurusan_pilihan_id', $j->id)->where('status', 'diterima')->count(),
            'pending'  => PendaftarPpdb::where('jurusan_pilihan_id', $j->id)->whereIn('status', ['pending', 'verifikasi'])->count(),
        ]);

        return view('admin.dashboard', compact('stats', 'absensiChart', 'sppChart', 'ppdbChart'));
    }
}
