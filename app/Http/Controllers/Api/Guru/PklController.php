<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\PenempatanPkl;
use App\Models\Semester;

class PklController extends Controller
{
    public function index()
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $bimbingans = PenempatanPkl::with(['siswa.kelas', 'tempatPkl', 'nilai'])
            ->where('guru_pembimbing_id', $guru->id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->get()
            ->map(fn($p) => [
                'id'              => $p->id,
                'siswa'           => $p->siswa?->nama,
                'kelas'           => $p->siswa?->kelas?->nama_kelas,
                'tempat'          => $p->tempatPkl?->nama,
                'periode_mulai'   => $p->periode_mulai?->toDateString(),
                'periode_selesai' => $p->periode_selesai?->toDateString(),
                'status'          => $p->status,
                'sudah_dinilai'   => $p->nilai !== null,
            ]);

        return response()->json(['data' => $bimbingans]);
    }
}
