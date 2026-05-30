<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Resources\NilaiResource;
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

        $jadwalIds = Jadwal::where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->pluck('id');

        $nilais = Nilai::with(['jadwal.mapel'])
            ->where('siswa_id', $siswa->id)
            ->whereIn('jadwal_id', $jadwalIds)
            ->get();

        return NilaiResource::collection($nilais);
    }
}
