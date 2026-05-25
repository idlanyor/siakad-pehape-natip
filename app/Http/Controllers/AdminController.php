<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Matkul;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_siswa'       => Siswa::count(),
            'total_guru'        => Guru::count(),
            'total_kelas'       => Kelas::count(),
            'total_jurusan'     => Jurusan::count(),
            'total_mapel'       => Matkul::count(),
            'tahun_ajaran'      => TahunAjaran::aktif()?->nama ?? '-',
            'semester_aktif'    => Semester::aktif()?->semester ?? '-',
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
