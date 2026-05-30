<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Resources\PengumumanResource;
use App\Models\Pengumuman;

class PengumumanController extends Controller
{
    public function index()
    {
        $pengumumans = Pengumuman::forUser(auth()->user())
            ->orderBy('published_at', 'desc')
            ->paginate(15);

        return PengumumanResource::collection($pengumumans);
    }
}
