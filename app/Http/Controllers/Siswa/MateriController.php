<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Materi;
use App\Models\Semester;
use App\Models\Siswa;

class MateriController extends Controller
{
    public function index()
    {
        $siswa    = Siswa::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $jadwals = Jadwal::with(['mapel', 'guru'])
            ->where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->withCount(['materis' => fn($q) => $q->where('is_published', true)])
            ->get();

        return view('siswa.materi.index', compact('siswa', 'jadwals', 'semester'));
    }

    public function showByJadwal(Jadwal $jadwal)
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        abort_if($jadwal->kelas_id !== $siswa->kelas_id, 403);

        $jadwal->load(['mapel', 'guru', 'semester.tahunAjaran']);
        $materis = Materi::where('jadwal_id', $jadwal->id)
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('siswa.materi.show', compact('jadwal', 'materis'));
    }

    public function detail(Materi $materi)
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        abort_if($materi->jadwal->kelas_id !== $siswa->kelas_id, 403);
        abort_if(! $materi->is_published, 404);

        $materi->load(['jadwal.mapel', 'guru']);
        return view('siswa.materi.detail', compact('materi'));
    }
}
