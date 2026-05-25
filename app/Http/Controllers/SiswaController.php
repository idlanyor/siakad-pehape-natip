<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Jadwal;
use App\Models\Semester;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function dashboard()
    {
        $siswa = Siswa::with(['jurusan', 'kelas'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $hariIni = now()->locale('id')->isoFormat('dddd');
        $hariMap = ['Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu','Sunday'=>'Minggu'];
        $hariIni = $hariMap[now()->englishDayOfWeek] ?? now()->englishDayOfWeek;

        $jadwalHariIni = Jadwal::with(['matkul', 'guru'])
            ->where('kelas_id', $siswa->kelas_id)
            ->where('hari', $hariIni)
            ->orderBy('jam_mulai')
            ->get();

        return view('siswa.dashboard', compact('siswa', 'jadwalHariIni'));
    }

    public function jadwal()
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $jadwals = Jadwal::with(['matkul', 'guru'])
            ->where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        $grouped = $jadwals->groupBy('hari');

        return view('siswa.jadwal', compact('jadwals', 'grouped', 'siswa', 'semester'));
    }
}
