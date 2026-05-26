<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HalamanStatis;
use Illuminate\Http\Request;

class HalamanStatisController extends Controller
{
    public function index()
    {
        $halamanList = HalamanStatis::orderBy('urutan')->orderBy('judul')->get();
        return view('admin.cms.halaman.index', compact('halamanList'));
    }

    public function create()
    {
        return view('admin.cms.halaman.form', ['halaman' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'slug'     => 'required|string|max:100|unique:halaman_statis,slug|regex:/^[a-z0-9\-]+$/',
            'judul'    => 'required|string|max:255',
            'konten'   => 'nullable|string',
            'meta_desc' => 'nullable|string|max:255',
            'urutan'   => 'nullable|integer',
        ]);

        HalamanStatis::create([
            'slug'         => $request->slug,
            'judul'        => $request->judul,
            'konten'       => $request->konten,
            'meta_desc'    => $request->meta_desc,
            'is_published' => $request->boolean('is_published'),
            'urutan'       => $request->urutan ?? 0,
        ]);

        return redirect()->route('admin.cms.halaman.index')
            ->with('success', 'Halaman berhasil ditambahkan.');
    }

    public function edit(HalamanStatis $halaman)
    {
        return view('admin.cms.halaman.form', compact('halaman'));
    }

    public function update(Request $request, HalamanStatis $halaman)
    {
        $request->validate([
            'slug'     => 'required|string|max:100|unique:halaman_statis,slug,' . $halaman->id . '|regex:/^[a-z0-9\-]+$/',
            'judul'    => 'required|string|max:255',
            'konten'   => 'nullable|string',
            'meta_desc' => 'nullable|string|max:255',
            'urutan'   => 'nullable|integer',
        ]);

        $halaman->update([
            'slug'         => $request->slug,
            'judul'        => $request->judul,
            'konten'       => $request->konten,
            'meta_desc'    => $request->meta_desc,
            'is_published' => $request->boolean('is_published'),
            'urutan'       => $request->urutan ?? 0,
        ]);

        return redirect()->route('admin.cms.halaman.index')
            ->with('success', 'Halaman berhasil diperbarui.');
    }

    public function destroy(HalamanStatis $halaman)
    {
        $halaman->delete();
        return back()->with('success', 'Halaman berhasil dihapus.');
    }
}
