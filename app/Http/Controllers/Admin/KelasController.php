<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KelasRequest;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Modules\Ujian\Models\Ujian;

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
        $gurus = Guru::all();

        return view('admin.kelas.form', ['kelas' => null, 'jurusans' => $jurusans, 'gurus' => $gurus]);
    }

    public function store(KelasRequest $request)
    {
        Kelas::create($request->only('nama_kelas', 'jurusan_id', 'tingkat', 'wali_kelas_id'));

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kela)
    {
        $jurusans = Jurusan::all();
        $gurus = Guru::all();

        return view('admin.kelas.form', ['kelas' => $kela, 'jurusans' => $jurusans, 'gurus' => $gurus]);
    }

    public function update(KelasRequest $request, Kelas $kela)
    {
        $kela->update($request->only('nama_kelas', 'jurusan_id', 'tingkat', 'wali_kelas_id'));

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela)
    {
        // Hasil ujian online merujuk ke kelas ini (FK restrict).
        if (Ujian::withTrashed()->where('kelas_id', $kela->id)->exists()) {
            return redirect()->route('admin.kelas.index')->with('error', 'Kelas tidak bisa dihapus karena sudah dipakai di ujian online.');
        }

        $kela->delete();

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
