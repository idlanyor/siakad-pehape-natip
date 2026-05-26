<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Nilai;
use App\Models\Semester;
use App\Models\Siswa;

class NilaiController extends Controller
{
    public function index()
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $jadwals = Jadwal::with(['mapel', 'guru'])
            ->where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->get();

        $nilais = Nilai::where('siswa_id', $siswa->id)
            ->whereIn('jadwal_id', $jadwals->pluck('id'))
            ->get()
            ->keyBy('jadwal_id');

        return view('siswa.nilai', compact('siswa', 'jadwals', 'nilais', 'semester'));
    }

    public function raport()
    {
        $siswa = Siswa::with(['kelas', 'jurusan'])
            ->where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $jadwals = Jadwal::with(['mapel'])
            ->where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->get();

        $nilais = Nilai::where('siswa_id', $siswa->id)
            ->whereIn('jadwal_id', $jadwals->pluck('id'))
            ->get()
            ->keyBy('jadwal_id');

        return view('siswa.raport', compact('siswa', 'semester', 'jadwals', 'nilais'));
    }
}
