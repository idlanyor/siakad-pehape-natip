<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurusan;
use App\Models\Mapel;
use App\Models\Semester;
use App\Models\Siswa;
use App\Modules\Ujian\Services\PenilaianService;

class GuruDashboardController extends Controller
{
    public function index()
    {
        $guru = Guru::where('user_id', auth()->id())->first();
        $semester = Semester::aktif();

        // Jawaban essay ujian online yang perlu dikoreksi
        $perluKoreksi = $guru ? PenilaianService::jumlahPerluKoreksi($guru) : 0;

        $stats = [
            'total_siswa' => Siswa::count(),
            'total_mapel' => Mapel::count(),
            'total_jurusan' => Jurusan::count(),
            'total_jadwal' => $guru ? Jadwal::where('guru_id', $guru->id)->count() : 0,
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
            ->when($semester, fn ($q) => $q->where('semester_id', $semester->id))
            ->urutHari()
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
            if ($semester) {
                $q->where('semester_id', $semester->id);
            }
        })->with(['jadwals' => function ($q) use ($guru, $semester) {
            $q->where('guru_id', $guru->id);
            if ($semester) {
                $q->where('semester_id', $semester->id);
            }
        }, 'jurusan', 'gurus'])->get();

        return view('guru.mapel', compact('mapels', 'guru', 'semester'));
    }
}
