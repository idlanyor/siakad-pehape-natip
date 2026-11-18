<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Resources\JadwalResource;
use App\Models\Jadwal;
use App\Models\Semester;
use App\Models\Siswa;

class JadwalController extends Controller
{
    public function index()
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $jadwals = Jadwal::with(['mapel', 'guru', 'kelas'])
            ->where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->urutHari()
            ->orderBy('jam_mulai')
            ->get();

        return JadwalResource::collection($jadwals);
    }
}
