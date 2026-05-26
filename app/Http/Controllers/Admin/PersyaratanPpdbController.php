<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersyaratanPpdb;
use Illuminate\Http\Request;

class PersyaratanPpdbController extends Controller
{
    public function index()
    {
        $persyaratanList = PersyaratanPpdb::orderBy('urutan')->get();
        return view('admin.ppdb.persyaratan.index', compact('persyaratanList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan'    => 'nullable|integer',
        ]);

        PersyaratanPpdb::create([
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
            'urutan'    => $request->urutan ?? (PersyaratanPpdb::max('urutan') + 1),
            'is_aktif'  => $request->boolean('is_aktif', true),
        ]);

        return back()->with('success', 'Persyaratan berhasil ditambahkan.');
    }

    public function update(Request $request, PersyaratanPpdb $persyaratan)
    {
        $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'urutan'    => 'nullable|integer',
        ]);

        $persyaratan->update([
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
            'urutan'    => $request->urutan ?? $persyaratan->urutan,
            'is_aktif'  => $request->boolean('is_aktif'),
        ]);

        return back()->with('success', 'Persyaratan berhasil diperbarui.');
    }

    public function destroy(PersyaratanPpdb $persyaratan)
    {
        $persyaratan->delete();
        return back()->with('success', 'Persyaratan berhasil dihapus.');
    }
}
