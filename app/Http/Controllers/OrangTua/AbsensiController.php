<?php

namespace App\Http\Controllers\OrangTua;

use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Semester;

class AbsensiController extends BaseOrangTuaController
{
    public function index()
    {
        $ortu = $this->orangTua();
        $anak = $this->anakAktif($ortu);
        abort_if(! $anak, 404, 'Belum ada data anak.');

        $semester = Semester::aktif();

        $jadwals = Jadwal::with(['mapel', 'guru'])
            ->where('kelas_id', $anak->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->get();

        $rekap = [];
        foreach ($jadwals as $jadwal) {
            $absensi = Absensi::where('jadwal_id', $jadwal->id)
                ->where('siswa_id', $anak->id)
                ->get();

            $total  = $absensi->count();
            $hadir  = $absensi->where('status', 'Hadir')->count();
            $izin   = $absensi->where('status', 'Izin')->count();
            $sakit  = $absensi->where('status', 'Sakit')->count();
            $alpa   = $absensi->where('status', 'Alpa')->count();
            $persen = $total > 0 ? round(($hadir / $total) * 100) : 0;

            $rekap[$jadwal->id] = compact('total', 'hadir', 'izin', 'sakit', 'alpa', 'persen');
        }

        return view('orangtua.absensi', compact('ortu', 'anak', 'jadwals', 'rekap', 'semester'));
    }
}
