<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AbsensiExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\Siswa;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller
{
    public function index()
    {
        $semester = Semester::aktif();
        $kelas    = Kelas::with('jurusan')->get();

        // Statistik global
        $stats = [
            'total_hadir' => Absensi::where('status', 'Hadir')->count(),
            'total_izin'  => Absensi::where('status', 'Izin')->count(),
            'total_sakit' => Absensi::where('status', 'Sakit')->count(),
            'total_alpa'  => Absensi::where('status', 'Alpa')->count(),
        ];

        // Siswa dengan Alpa >= threshold
        $threshold = Absensi::THRESHOLD_ALPA;
        $siswaAlpa = Siswa::with(['kelas', 'jurusan'])
            ->whereHas('absensi', function ($q) use ($threshold) {
                $q->where('status', 'Alpa')
                  ->selectRaw('siswa_id, COUNT(*) as jumlah')
                  ->groupBy('siswa_id')
                  ->havingRaw('COUNT(*) >= ?', [$threshold]);
            })
            ->get()
            ->map(function ($siswa) {
                $siswa->jumlah_alpa = $siswa->absensi()->where('status', 'Alpa')->count();
                return $siswa;
            });

        return view('admin.absensi.index', compact('stats', 'kelas', 'semester', 'siswaAlpa', 'threshold'));
    }

    public function detail(Jadwal $jadwal)
    {
        $siswas   = Siswa::where('kelas_id', $jadwal->kelas_id)->orderBy('nama')->get();
        [$pertemuan, $pivot] = Absensi::rekapJadwal($jadwal);

        return view('admin.absensi.detail', compact('jadwal', 'siswas', 'pertemuan', 'pivot'));
    }

    public function exportAbsensi(Jadwal $jadwal)
    {
        $nama = 'Absensi_' . str_replace(' ', '_', $jadwal->mapel->nama_mapel ?? 'Mapel') . '_' . ($jadwal->kelas->nama_kelas ?? '') . '.xlsx';
        return Excel::download(new AbsensiExport($jadwal), $nama);
    }
}
