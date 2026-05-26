<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtikelController extends Controller
{
    public function index(Request $request)
    {
        $query = Artikel::with('penulis')->orderBy('created_at', 'desc');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('status')) {
            $request->status === 'published'
                ? $query->where('is_published', true)
                : $query->where('is_published', false);
        }

        $artikels    = $query->paginate(20)->withQueryString();
        $kategoriList = Artikel::KATEGORI;

        return view('admin.cms.artikel.index', compact('artikels', 'kategoriList'));
    }

    public function create()
    {
        $kategoriList = Artikel::KATEGORI;
        return view('admin.cms.artikel.form', ['artikel' => null, 'kategoriList' => $kategoriList]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'        => 'required|string|max:255',
            'kategori'     => 'required|in:' . implode(',', array_keys(Artikel::KATEGORI)),
            'ringkasan'    => 'nullable|string|max:500',
            'konten'       => 'nullable|string',
            'thumbnail'    => 'nullable|image|max:3072',
            'published_at' => 'nullable|date',
        ]);

        $data = $request->only(['judul', 'ringkasan', 'konten', 'kategori', 'published_at']);
        $data['slug']        = Artikel::generateSlug($request->judul);
        $data['is_published'] = $request->boolean('is_published');
        $data['dibuat_oleh'] = auth()->id();

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('artikel/thumbnail', 'public');
        }

        $artikel = Artikel::create($data);

        return redirect()->route('admin.cms.artikel.index')
            ->with('success', 'Artikel berhasil disimpan.');
    }

    public function edit(Artikel $artikel)
    {
        $kategoriList = Artikel::KATEGORI;
        return view('admin.cms.artikel.form', compact('artikel', 'kategoriList'));
    }

    public function update(Request $request, Artikel $artikel)
    {
        $request->validate([
            'judul'        => 'required|string|max:255',
            'kategori'     => 'required|in:' . implode(',', array_keys(Artikel::KATEGORI)),
            'ringkasan'    => 'nullable|string|max:500',
            'konten'       => 'nullable|string',
            'thumbnail'    => 'nullable|image|max:3072',
            'published_at' => 'nullable|date',
        ]);

        $data = $request->only(['judul', 'ringkasan', 'konten', 'kategori', 'published_at']);
        $data['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('thumbnail')) {
            if ($artikel->thumbnail) Storage::disk('public')->delete($artikel->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('artikel/thumbnail', 'public');
        }

        $artikel->update($data);

        return redirect()->route('admin.cms.artikel.index')
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Artikel $artikel)
    {
        if ($artikel->thumbnail) Storage::disk('public')->delete($artikel->thumbnail);
        $artikel->delete();
        return back()->with('success', 'Artikel berhasil dihapus.');
    }
}
