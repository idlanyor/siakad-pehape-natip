<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        $mapels = Mapel::with('jadwals')->get();
        return view('admin.mapel.index', compact('mapels'));
    }

    public function create()
    {
        return view('admin.mapel.form', ['mapel' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode'          => 'required|string|max:20|unique:mapels,kode',
            'nama_mapel'   => 'required|string|max:100',
            'dosen_pengampu'=> 'required|string|max:100',
        ]);

        Mapel::create($request->only('kode', 'nama_mapel', 'dosen_pengampu'));

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Mapel $mapel)
    {
        return view('admin.mapel.form', ['mapel' => $mapel]);
    }

    public function update(Request $request, Mapel $mapel)
    {
        $request->validate([
            'kode'          => 'required|string|max:20|unique:mapels,kode,' . $mapel->id,
            'nama_mapel'   => 'required|string|max:100',
            'dosen_pengampu'=> 'required|string|max:100',
        ]);

        $mapel->update($request->only('kode', 'nama_mapel', 'dosen_pengampu'));

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Mapel $mapel)
    {
        $mapel->delete();
        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
