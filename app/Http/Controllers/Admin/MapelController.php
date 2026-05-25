<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Matkul;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        $matkuls = Matkul::with('jadwals')->get();
        return view('admin.mapel.index', compact('matkuls'));
    }

    public function create()
    {
        return view('admin.mapel.form', ['matkul' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode'          => 'required|string|max:20|unique:matkuls,kode',
            'nama_matkul'   => 'required|string|max:100',
            'dosen_pengampu'=> 'required|string|max:100',
        ]);

        Matkul::create($request->only('kode', 'nama_matkul', 'dosen_pengampu'));

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Matkul $mapel)
    {
        return view('admin.mapel.form', ['matkul' => $mapel]);
    }

    public function update(Request $request, Matkul $mapel)
    {
        $request->validate([
            'kode'          => 'required|string|max:20|unique:matkuls,kode,' . $mapel->id,
            'nama_matkul'   => 'required|string|max:100',
            'dosen_pengampu'=> 'required|string|max:100',
        ]);

        $mapel->update($request->only('kode', 'nama_matkul', 'dosen_pengampu'));

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Matkul $mapel)
    {
        $mapel->delete();
        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
