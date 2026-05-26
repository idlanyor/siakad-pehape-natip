<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TempatPkl;
use Illuminate\Http\Request;

class TempatPklController extends Controller
{
    public function index()
    {
        $tempats = TempatPkl::withCount('penempatans')->get();
        return view('admin.pkl.tempat.index', compact('tempats'));
    }

    public function create()
    {
        return view('admin.pkl.tempat.form', ['tempat' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'         => 'required|string|max:150|unique:tempat_pkls,nama',
            'bidang_usaha' => 'nullable|string|max:100',
            'alamat'       => 'nullable|string',
            'kontak_person'=> 'nullable|string|max:100',
            'no_hp'        => 'nullable|string|max:20',
            'kuota'        => 'required|integer|min:1|max:999',
        ]);
        TempatPkl::create($request->only('nama', 'bidang_usaha', 'alamat', 'kontak_person', 'no_hp', 'kuota'));
        return redirect()->route('admin.pkl-tempat.index')->with('success', 'Tempat PKL berhasil ditambahkan.');
    }

    public function edit(TempatPkl $pklTempat)
    {
        return view('admin.pkl.tempat.form', ['tempat' => $pklTempat]);
    }

    public function update(Request $request, TempatPkl $pklTempat)
    {
        $request->validate([
            'nama'         => 'required|string|max:150|unique:tempat_pkls,nama,' . $pklTempat->id,
            'bidang_usaha' => 'nullable|string|max:100',
            'alamat'       => 'nullable|string',
            'kontak_person'=> 'nullable|string|max:100',
            'no_hp'        => 'nullable|string|max:20',
            'kuota'        => 'required|integer|min:1|max:999',
        ]);
        $pklTempat->update($request->only('nama', 'bidang_usaha', 'alamat', 'kontak_person', 'no_hp', 'kuota'));
        return redirect()->route('admin.pkl-tempat.index')->with('success', 'Tempat PKL berhasil diperbarui.');
    }

    public function destroy(TempatPkl $pklTempat)
    {
        $pklTempat->delete();
        return redirect()->route('admin.pkl-tempat.index')->with('success', 'Tempat PKL berhasil dihapus.');
    }
}
