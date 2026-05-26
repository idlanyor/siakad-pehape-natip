<?php

namespace App\Http\Controllers;

use App\Models\Galeri;

class GaleriPublikController extends Controller
{
    public function index()
    {
        $galeris = Galeri::published()
            ->withCount('items')
            ->orderBy('tanggal', 'desc')
            ->paginate(12);

        return view('public.galeri.index', compact('galeris'));
    }

    public function show(Galeri $galeri)
    {
        abort_if(!$galeri->is_published, 404);
        $galeri->load('items');

        return view('public.galeri.show', compact('galeri'));
    }
}
