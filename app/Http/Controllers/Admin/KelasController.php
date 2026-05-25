<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with(['jurusan', 'waliKelas'])->withCount('siswas')->get();
        return view('admin.kelas.index', compact('kelas'));
    }

    public function create()
    {
        $jurusans = Jurusan::all();
        $gurus    = Guru::all();
        return view('admin.kelas.form', ['kelas' => null, 'jurusans' => $jurusans, 'gurus' => $gurus]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas'    => 'required|string|max:50',
            'jurusan_id'    => 'required|exists:jurusans,id',
            'tingkat'       => 'required|in:X,XI,XII',
            'wali_kelas_id' => 'nullable|exists:gurus,id',
        ]);

        Kelas::create($request->only('nama_kelas', 'jurusan_id', 'tingkat', 'wali_kelas_id'));

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kela)
    {
        $jurusans = Jurusan::all();
        $gurus    = Guru::all();
        return view('admin.kelas.form', ['kelas' => $kela, 'jurusans' => $jurusans, 'gurus' => $gurus]);
    }

    public function update(Request $request, Kelas $kela)
    {
        $request->validate([
            'nama_kelas'    => 'required|string|max:50',
            'jurusan_id'    => 'required|exists:jurusans,id',
            'tingkat'       => 'required|in:X,XI,XII',
            'wali_kelas_id' => 'nullable|exists:gurus,id',
        ]);

        $kela->update($request->only('nama_kelas', 'jurusan_id', 'tingkat', 'wali_kelas_id'));

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela)
    {
        $kela->delete();
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
