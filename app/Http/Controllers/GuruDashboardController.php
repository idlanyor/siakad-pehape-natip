<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Mapel;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Jurusan;
use App\Models\SoalUjian;

class GuruDashboardController extends Controller
{
    public function index()
    {
        $guru = Guru::where('user_id', auth()->id())->first();
        $semester = Semester::aktif();

        // Hitung essay yang perlu dikoreksi
        $perluKoreksi = 0;
        if ($guru) {
            $perluKoreksi = SoalUjian::whereHas('ujian', function($q) use ($guru) {
                $q->where('guru_id', $guru->id);
            })
            ->where('tipe', 'essay')
            ->whereHas('jawabanSiswas', function($q) {
                $q->whereNotNull('jawaban_text')
                  ->whereNull('nilai');
            })
            ->count();
        }

        $stats = [
            'total_siswa'   => Siswa::count(),
            'total_mapel'   => Mapel::count(),
            'total_jurusan' => Jurusan::count(),
            'total_jadwal'  => $guru ? Jadwal::where('guru_id', $guru->id)->count() : 0,
            'perlu_koreksi' => $perluKoreksi,
        ];

        return view('guru.dashboard', compact('stats', 'guru'));
    }

    public function jadwal()
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $jadwals = Jadwal::with(['mapel', 'kelas'])
            ->where('guru_id', $guru->id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        $grouped = $jadwals->groupBy('hari');

        return view('guru.jadwal', compact('jadwals', 'grouped', 'guru', 'semester'));
    }

    public function mapel()
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $mapels = Mapel::whereHas('jadwals', function ($q) use ($guru, $semester) {
            $q->where('guru_id', $guru->id);
            if ($semester) $q->where('semester_id', $semester->id);
        })->with(['jadwals' => function ($q) use ($guru, $semester) {
            $q->where('guru_id', $guru->id);
            if ($semester) $q->where('semester_id', $semester->id);
        }, 'jurusan', 'gurus'])->get();

        return view('guru.mapel', compact('mapels', 'guru', 'semester'));
    }
}
