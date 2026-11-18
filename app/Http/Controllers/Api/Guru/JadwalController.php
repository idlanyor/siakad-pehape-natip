<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Http\Resources\JadwalResource;
use App\Models\Guru;
use App\Models\Jadwal;

class JadwalController extends Controller
{
    public function index()
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();

        $jadwals = Jadwal::with(['mapel', 'kelas', 'semester.tahunAjaran'])
            ->where('guru_id', $guru->id)
            ->urutHari()
            ->orderBy('jam_mulai')
            ->get();

        return JadwalResource::collection($jadwals);
    }
}
