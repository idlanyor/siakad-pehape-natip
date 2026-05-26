<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JurusanController extends Controller
{
    public function index()
    {
        $jurusans = Jurusan::withCount('siswas')->get();
        return view('admin.jurusan.index', compact('jurusans'));
    }

    public function create()
    {
        return view('admin.jurusan.form', ['jurusan' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:100|unique:jurusans,nama_jurusan',
            'gambar'       => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['nama_jurusan', 'deskripsi_publik', 'icon']);
        $data['is_aktif_ppdb'] = $request->boolean('is_aktif_ppdb');
        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('jurusan', 'public');
        }

        Jurusan::create($data);

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit(Jurusan $jurusan)
    {
        return view('admin.jurusan.form', compact('jurusan'));
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:100|unique:jurusans,nama_jurusan,' . $jurusan->id,
            'gambar'       => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['nama_jurusan', 'deskripsi_publik', 'icon']);
        $data['is_aktif_ppdb'] = $request->boolean('is_aktif_ppdb');
        if ($request->hasFile('gambar')) {
            if ($jurusan->gambar) Storage::disk('public')->delete($jurusan->gambar);
            $data['gambar'] = $request->file('gambar')->store('jurusan', 'public');
        }

        $jurusan->update($data);

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        if ($jurusan->gambar) Storage::disk('public')->delete($jurusan->gambar);
        $jurusan->delete();
        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil dihapus.');
    }
}
