<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahunAjarans = TahunAjaran::with('semesters')->get();
        return view('admin.tahun-ajaran.index', compact('tahunAjarans'));
    }

    public function create()
    {
        return view('admin.tahun-ajaran.form', ['tahunAjaran' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:20|unique:tahun_ajarans,nama',
        ]);

        $tahunAjaran = TahunAjaran::create(['nama' => $request->nama, 'aktif' => false]);

        // Auto-create 2 semesters
        Semester::create(['tahun_ajaran_id' => $tahunAjaran->id, 'semester' => 1, 'aktif' => false]);
        Semester::create(['tahun_ajaran_id' => $tahunAjaran->id, 'semester' => 2, 'aktif' => false]);

        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(TahunAjaran $tahunAjaran)
    {
        return view('admin.tahun-ajaran.form', compact('tahunAjaran'));
    }

    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        $request->validate([
            'nama' => 'required|string|max:20|unique:tahun_ajarans,nama,' . $tahunAjaran->id,
        ]);

        $tahunAjaran->update(['nama' => $request->nama]);

        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran)
    {
        $tahunAjaran->delete();
        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function setAktif(TahunAjaran $tahunAjaran)
    {
        TahunAjaran::query()->update(['aktif' => false]);
        $tahunAjaran->update(['aktif' => true]);

        return redirect()->route('admin.tahun-ajaran.index')->with('success', "Tahun ajaran {$tahunAjaran->nama} diset aktif.");
    }

    public function setSemesterAktif(Semester $semester)
    {
        Semester::query()->update(['aktif' => false]);
        $semester->update(['aktif' => true]);

        // Aktifkan juga tahun ajarannya
        TahunAjaran::query()->update(['aktif' => false]);
        $semester->tahunAjaran->update(['aktif' => true]);

        return redirect()->route('admin.tahun-ajaran.index')->with('success', "Semester {$semester->semester} ({$semester->tahunAjaran->nama}) diset aktif.");
    }
}
