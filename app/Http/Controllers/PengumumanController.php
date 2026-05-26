<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $pengumumans = Pengumuman::with('pembuat')
            ->forUser($user)
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('pengumuman.index', compact('pengumumans'));
    }

    public function show(Pengumuman $pengumuman)
    {
        // Pastikan pengumuman sudah dipublish (kecuali admin)
        if (!$pengumuman->is_published && auth()->user()->role !== 0) {
            abort(404);
        }

        return view('pengumuman.show', compact('pengumuman'));
    }
}
