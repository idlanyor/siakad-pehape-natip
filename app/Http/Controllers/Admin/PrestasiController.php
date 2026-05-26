<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestasi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrestasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Prestasi::with(['siswa', 'pencatat'])
            ->orderBy('tanggal', 'desc');

        if ($request->filled('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }

        $prestasis = $query->get();
        $siswas = Siswa::orderBy('nama')->get();

        return view('admin.kesiswaan.prestasi.index', compact('prestasis', 'siswas'));
    }

    public function create()
    {
        $siswas = Siswa::with(['kelas', 'jurusan'])->orderBy('nama')->get();
        $tingkats = ['Sekolah', 'Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional'];
        return view('admin.kesiswaan.prestasi.form', ['prestasi' => null, 'siswas' => $siswas, 'tingkats' => $tingkats]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id'      => 'required|exists:siswas,id',
            'tanggal'       => 'required|date',
            'nama_prestasi' => 'required|string|max:200',
            'tingkat'       => 'required|in:Sekolah,Kecamatan,Kabupaten/Kota,Provinsi,Nasional,Internasional',
            'keterangan'    => 'nullable|string',
            'foto'          => 'nullable|image|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('prestasi', 'public');
        }

        Prestasi::create([
            'siswa_id'      => $request->siswa_id,
            'tanggal'       => $request->tanggal,
            'nama_prestasi' => $request->nama_prestasi,
            'tingkat'       => $request->tingkat,
            'keterangan'    => $request->keterangan,
            'foto'          => $fotoPath,
            'dicatat_oleh'  => auth()->id(),
        ]);

        return redirect()->route('admin.prestasi.index')
            ->with('success', 'Data prestasi berhasil ditambahkan.');
    }

    public function edit(Prestasi $prestasi)
    {
        $siswas = Siswa::with(['kelas', 'jurusan'])->orderBy('nama')->get();
        $tingkats = ['Sekolah', 'Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional'];
        return view('admin.kesiswaan.prestasi.form', compact('prestasi', 'siswas', 'tingkats'));
    }

    public function update(Request $request, Prestasi $prestasi)
    {
        $request->validate([
            'siswa_id'      => 'required|exists:siswas,id',
            'tanggal'       => 'required|date',
            'nama_prestasi' => 'required|string|max:200',
            'tingkat'       => 'required|in:Sekolah,Kecamatan,Kabupaten/Kota,Provinsi,Nasional,Internasional',
            'keterangan'    => 'nullable|string',
            'foto'          => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['siswa_id', 'tanggal', 'nama_prestasi', 'tingkat', 'keterangan']);

        if ($request->hasFile('foto')) {
            if ($prestasi->foto) {
                Storage::disk('public')->delete($prestasi->foto);
            }
            $data['foto'] = $request->file('foto')->store('prestasi', 'public');
        }

        $prestasi->update($data);

        return redirect()->route('admin.prestasi.index')
            ->with('success', 'Data prestasi berhasil diperbarui.');
    }

    public function destroy(Prestasi $prestasi)
    {
        if ($prestasi->foto) {
            Storage::disk('public')->delete($prestasi->foto);
        }
        $prestasi->delete();
        return redirect()->route('admin.prestasi.index')
            ->with('success', 'Data prestasi berhasil dihapus.');
    }
}
