<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Semester;
use App\Models\Siswa;

class AbsensiController extends Controller
{
    public function index()
    {
        $siswa    = Siswa::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        // Jadwal kelas siswa di semester aktif
        $jadwals = Jadwal::with(['mapel', 'guru'])
            ->where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->get();

        // Rekap per jadwal
        $rekap = [];
        foreach ($jadwals as $jadwal) {
            $absensi = Absensi::where('jadwal_id', $jadwal->id)
                ->where('siswa_id', $siswa->id)
                ->get();

            $total  = $absensi->count();
            $hadir  = $absensi->where('status', 'Hadir')->count();
            $izin   = $absensi->where('status', 'Izin')->count();
            $sakit  = $absensi->where('status', 'Sakit')->count();
            $alpa   = $absensi->where('status', 'Alpa')->count();
            $persen = $total > 0 ? round(($hadir / $total) * 100) : 0;

            $rekap[$jadwal->id] = compact('total', 'hadir', 'izin', 'sakit', 'alpa', 'persen');
        }

        return view('siswa.absensi', compact('siswa', 'jadwals', 'rekap', 'semester'));
    }
}
