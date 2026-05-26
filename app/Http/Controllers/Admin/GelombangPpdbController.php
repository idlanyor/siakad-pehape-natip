<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GelombangPpdb;
use Illuminate\Http\Request;

class GelombangPpdbController extends Controller
{
    public function index()
    {
        $gelombangList = GelombangPpdb::orderBy('tanggal_buka', 'desc')->get();
        return view('admin.ppdb.gelombang.index', compact('gelombangList'));
    }

    public function create()
    {
        return view('admin.ppdb.gelombang.form', ['gelombang' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'           => 'required|string|max:100',
            'tahun_ajaran'   => 'required|string|max:10',
            'tanggal_buka'   => 'required|date',
            'tanggal_tutup'  => 'required|date|after:tanggal_buka',
            'kuota'          => 'nullable|integer|min:0',
            'keterangan'     => 'nullable|string',
        ]);

        GelombangPpdb::create([
            'nama'          => $request->nama,
            'tahun_ajaran'  => $request->tahun_ajaran,
            'tanggal_buka'  => $request->tanggal_buka,
            'tanggal_tutup' => $request->tanggal_tutup,
            'kuota'         => $request->kuota ?? 0,
            'keterangan'    => $request->keterangan,
            'is_aktif'      => $request->boolean('is_aktif'),
        ]);

        return redirect()->route('admin.ppdb.gelombang.index')
            ->with('success', 'Gelombang PPDB berhasil ditambahkan.');
    }

    public function edit(GelombangPpdb $gelombang)
    {
        return view('admin.ppdb.gelombang.form', compact('gelombang'));
    }

    public function update(Request $request, GelombangPpdb $gelombang)
    {
        $request->validate([
            'nama'          => 'required|string|max:100',
            'tahun_ajaran'  => 'required|string|max:10',
            'tanggal_buka'  => 'required|date',
            'tanggal_tutup' => 'required|date|after:tanggal_buka',
            'kuota'         => 'nullable|integer|min:0',
        ]);

        $gelombang->update([
            'nama'          => $request->nama,
            'tahun_ajaran'  => $request->tahun_ajaran,
            'tanggal_buka'  => $request->tanggal_buka,
            'tanggal_tutup' => $request->tanggal_tutup,
            'kuota'         => $request->kuota ?? 0,
            'keterangan'    => $request->keterangan,
            'is_aktif'      => $request->boolean('is_aktif'),
        ]);

        return redirect()->route('admin.ppdb.gelombang.index')
            ->with('success', 'Gelombang PPDB berhasil diperbarui.');
    }

    public function destroy(GelombangPpdb $gelombang)
    {
        $gelombang->delete();
        return back()->with('success', 'Gelombang berhasil dihapus.');
    }
}
