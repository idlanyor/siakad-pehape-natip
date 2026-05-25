<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Matkul;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Jurusan;

class DosenController extends Controller
{
    public function index()
    {
        $guru = Guru::where('user_id', auth()->id())->first();
        $semester = Semester::aktif();

        $stats = [
            'total_siswa'   => Siswa::count(),
            'total_mapel'   => Matkul::count(),
            'total_jurusan' => Jurusan::count(),
            'total_jadwal'  => $guru ? Jadwal::where('guru_id', $guru->id)->count() : 0,
        ];

        return view('dosen.dashboard', compact('stats', 'guru'));
    }

    public function jadwal()
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $jadwals = Jadwal::with(['matkul', 'kelas'])
            ->where('guru_id', $guru->id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        $grouped = $jadwals->groupBy('hari');

        return view('dosen.jadwal', compact('jadwals', 'grouped', 'guru', 'semester'));
    }
}
