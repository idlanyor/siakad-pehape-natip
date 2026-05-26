<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Http\Request;

class BeritaController extends Controller
{
    public function index(Request $request)
    {
        $query = Artikel::published()->orderBy('published_at', 'desc');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('q')) {
            $query->where(fn($q) => $q->where('judul', 'LIKE', '%' . $request->q . '%')
                ->orWhere('ringkasan', 'LIKE', '%' . $request->q . '%'));
        }

        $artikels    = $query->paginate(9)->withQueryString();
        $kategoriList = Artikel::KATEGORI;
        $terbaru     = Artikel::published()->orderBy('published_at', 'desc')->limit(5)->get();

        return view('public.berita.index', compact('artikels', 'kategoriList', 'terbaru'));
    }

    public function show(Artikel $artikel)
    {
        abort_if(!$artikel->is_published, 404);

        $terkait = Artikel::published()
            ->where('kategori', $artikel->kategori)
            ->where('id', '!=', $artikel->id)
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return view('public.berita.show', compact('artikel', 'terkait'));
    }
}
