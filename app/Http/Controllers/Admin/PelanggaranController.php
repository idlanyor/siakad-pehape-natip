<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use Illuminate\Http\Request;

class PelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggaran::with(['siswa', 'pencatat'])
            ->orderBy('tanggal', 'desc');

        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        $pelanggarans = $query->get();
        $siswas = Siswa::orderBy('nama')->get();

        return view('admin.kesiswaan.pelanggaran.index', compact('pelanggarans', 'siswas'));
    }

    public function create()
    {
        $siswas = Siswa::with(['kelas', 'jurusan'])->orderBy('nama')->get();
        return view('admin.kesiswaan.pelanggaran.form', ['pelanggaran' => null, 'siswas' => $siswas]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id'  => 'required|exists:siswas,id',
            'tanggal'   => 'required|date',
            'jenis'     => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'poin'      => 'required|integer|min:0',
        ]);

        Pelanggaran::create([
            'siswa_id'    => $request->siswa_id,
            'tanggal'     => $request->tanggal,
            'jenis'       => $request->jenis,
            'deskripsi'   => $request->deskripsi,
            'poin'        => $request->poin,
            'dicatat_oleh' => auth()->id(),
        ]);

        return redirect()->route('admin.pelanggaran.index')
            ->with('success', 'Data pelanggaran berhasil ditambahkan.');
    }

    public function edit(Pelanggaran $pelanggaran)
    {
        $siswas = Siswa::with(['kelas', 'jurusan'])->orderBy('nama')->get();
        return view('admin.kesiswaan.pelanggaran.form', compact('pelanggaran', 'siswas'));
    }

    public function update(Request $request, Pelanggaran $pelanggaran)
    {
        $request->validate([
            'siswa_id'  => 'required|exists:siswas,id',
            'tanggal'   => 'required|date',
            'jenis'     => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'poin'      => 'required|integer|min:0',
        ]);

        $pelanggaran->update([
            'siswa_id'  => $request->siswa_id,
            'tanggal'   => $request->tanggal,
            'jenis'     => $request->jenis,
            'deskripsi' => $request->deskripsi,
            'poin'      => $request->poin,
        ]);

        return redirect()->route('admin.pelanggaran.index')
            ->with('success', 'Data pelanggaran berhasil diperbarui.');
    }

    public function destroy(Pelanggaran $pelanggaran)
    {
        $pelanggaran->delete();
        return redirect()->route('admin.pelanggaran.index')
            ->with('success', 'Data pelanggaran berhasil dihapus.');
    }
}
