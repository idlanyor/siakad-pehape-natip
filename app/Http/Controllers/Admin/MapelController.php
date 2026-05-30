<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MapelRequest;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Mapel;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        $mapels = Mapel::with(['jadwals', 'jurusan', 'gurus'])->get();
        return view('admin.mapel.index', compact('mapels'));
    }

    public function create()
    {
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $gurus    = Guru::orderBy('nama')->get();
        return view('admin.mapel.form', ['mapel' => null, 'jurusans' => $jurusans, 'gurus' => $gurus]);
    }

    public function store(MapelRequest $request)
    {
        $mapel = Mapel::create($request->only('kode', 'nama_mapel', 'jurusan_id'));

        if ($request->filled('guru_ids')) {
            $mapel->gurus()->sync($request->guru_ids);
        }

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Mapel $mapel)
    {
        $mapel->load('gurus');
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $gurus    = Guru::orderBy('nama')->get();
        return view('admin.mapel.form', compact('mapel', 'jurusans', 'gurus'));
    }

    public function update(MapelRequest $request, Mapel $mapel)
    {
        $mapel->update($request->only('kode', 'nama_mapel', 'jurusan_id'));
        $mapel->gurus()->sync($request->guru_ids ?? []);

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Mapel $mapel)
    {
        $mapel->delete();
        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
