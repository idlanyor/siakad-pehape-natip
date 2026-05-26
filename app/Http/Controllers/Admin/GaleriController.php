<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Galeri;
use App\Models\GaleriItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaleriController extends Controller
{
    public function index()
    {
        $galeris = Galeri::withCount('items')->orderBy('tanggal', 'desc')->get();
        return view('admin.cms.galeri.index', compact('galeris'));
    }

    public function create()
    {
        return view('admin.cms.galeri.form', ['galeri' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'         => 'required|string|max:255',
            'tanggal'      => 'required|date',
            'deskripsi'    => 'nullable|string',
            'cover'        => 'nullable|image|max:3072',
            'fotos'        => 'nullable|array',
            'fotos.*'      => 'image|max:3072',
        ]);

        $data = $request->only(['nama', 'tanggal', 'deskripsi']);
        $data['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('galeri/cover', 'public');
        }

        $galeri = Galeri::create($data);

        $this->saveItems($request, $galeri);

        return redirect()->route('admin.cms.galeri.show', $galeri)
            ->with('success', 'Galeri berhasil dibuat.');
    }

    public function show(Galeri $galeri)
    {
        $galeri->load('items');
        return view('admin.cms.galeri.show', compact('galeri'));
    }

    public function edit(Galeri $galeri)
    {
        return view('admin.cms.galeri.form', compact('galeri'));
    }

    public function update(Request $request, Galeri $galeri)
    {
        $request->validate([
            'nama'      => 'required|string|max:255',
            'tanggal'   => 'required|date',
            'deskripsi' => 'nullable|string',
            'cover'     => 'nullable|image|max:3072',
            'fotos'     => 'nullable|array',
            'fotos.*'   => 'image|max:3072',
        ]);

        $data = $request->only(['nama', 'tanggal', 'deskripsi']);
        $data['is_published'] = $request->boolean('is_published');

        if ($request->hasFile('cover')) {
            if ($galeri->cover) Storage::disk('public')->delete($galeri->cover);
            $data['cover'] = $request->file('cover')->store('galeri/cover', 'public');
        }

        $galeri->update($data);

        $this->saveItems($request, $galeri);

        return redirect()->route('admin.cms.galeri.show', $galeri)
            ->with('success', 'Galeri berhasil diperbarui.');
    }

    public function destroy(Galeri $galeri)
    {
        foreach ($galeri->items as $item) {
            Storage::disk('public')->delete($item->foto);
        }
        if ($galeri->cover) Storage::disk('public')->delete($galeri->cover);
        $galeri->delete();

        return redirect()->route('admin.cms.galeri.index')
            ->with('success', 'Galeri berhasil dihapus.');
    }

    public function destroyItem(GaleriItem $item)
    {
        Storage::disk('public')->delete($item->foto);
        $galeri = $item->galeri;
        $item->delete();

        return back()->with('success', 'Foto berhasil dihapus.');
    }

    private function saveItems(Request $request, Galeri $galeri): void
    {
        if (!$request->hasFile('fotos')) return;

        $lastUrutan = $galeri->items()->max('urutan') ?? 0;
        foreach ($request->file('fotos') as $i => $file) {
            $path = $file->store('galeri/foto', 'public');
            GaleriItem::create([
                'galeri_id'   => $galeri->id,
                'foto'        => $path,
                'keterangan'  => $request->keterangans[$i] ?? null,
                'urutan'      => $lastUrutan + $i + 1,
            ]);
        }
    }
}
