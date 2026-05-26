<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenempatanPkl;
use App\Models\Semester;

class PklController extends Controller
{
    public function index()
    {
        $semester = Semester::aktif();

        $penempatans = PenempatanPkl::with(['siswa.kelas', 'tempatPkl', 'guruPembimbing', 'nilai'])
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->get();

        $stats = [
            'total'   => $penempatans->count(),
            'aktif'   => $penempatans->where('status', 'aktif')->count(),
            'selesai' => $penempatans->where('status', 'selesai')->count(),
        ];

        return view('admin.pkl.index', compact('penempatans', 'stats', 'semester'));
    }

    public function detail(PenempatanPkl $penempatan)
    {
        $penempatan->load(['siswa.kelas', 'tempatPkl', 'guruPembimbing', 'jurnals', 'absensis', 'nilai']);
        return view('admin.pkl.detail', compact('penempatan'));
    }
}
