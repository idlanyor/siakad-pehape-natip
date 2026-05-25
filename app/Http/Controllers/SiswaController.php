<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Matkul;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function dashboard()
    {
        $siswa = Siswa::with('jurusan')
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('siswa.dashboard', compact('siswa'));
    }

    public function matkul()
    {
        $matkuls = Matkul::with('jadwals')->get();
        return view('siswa.matkul', compact('matkuls'));
    }
}
