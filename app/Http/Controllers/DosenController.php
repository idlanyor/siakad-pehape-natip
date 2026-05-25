<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Matkul;
use App\Models\Siswa;
use App\Models\Jurusan;

class DosenController extends Controller
{
    public function index()
    {
        $stats = [
            'total_siswa' => Siswa::count(),
            'total_mapel' => Matkul::count(),
            'total_jurusan' => Jurusan::count(),
        ];
        
        return view('dosen.dashboard', compact('stats'));
    }

    public function matkul()
    {
        $matkuls = Matkul::with('jadwals')->get();
        return view('dosen.matkul', compact('matkuls'));
    }
}
