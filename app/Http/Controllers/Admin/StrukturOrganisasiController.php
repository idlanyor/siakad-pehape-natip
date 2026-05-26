<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnggotaOrganisasi;
use App\Models\Guru;
use App\Models\JabatanOrganisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StrukturOrganisasiController extends Controller
{
    public function index()
    {
        $jabatans = JabatanOrganisasi::with(['parent', 'anggota'])
            ->orderBy('parent_id')
            ->orderBy('urutan')
            ->get();

        return view('admin.organisasi.index', compact('jabatans'));
    }

    public function create()
    {
        $parents = JabatanOrganisasi::whereNull('parent_id')->orderBy('urutan')->get();
        return view('admin.organisasi.form', ['jabatan' => null, 'parents' => $parents]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jabatan'  => 'required|string|max:150',
            'parent_id'     => 'nullable|exists:jabatan_organisasis,id',
            'tipe_hubungan' => 'required|in:komando,koordinasi,konsultasi',
            'urutan'        => 'nullable|integer|min:0',
            'deskripsi'     => 'nullable|string',
        ]);

        JabatanOrganisasi::create([
            'nama_jabatan'  => $request->nama_jabatan,
            'parent_id'     => $request->parent_id ?: null,
            'tipe_hubungan' => $request->tipe_hubungan,
            'urutan'        => $request->urutan ?? 0,
            'deskripsi'     => $request->deskripsi,
        ]);

        return redirect()->route('admin.organisasi.index')
            ->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(JabatanOrganisasi $organisasi)
    {
        $parents = JabatanOrganisasi::whereNull('parent_id')
            ->where('id', '!=', $organisasi->id)
            ->orderBy('urutan')
            ->get();

        return view('admin.organisasi.form', ['jabatan' => $organisasi, 'parents' => $parents]);
    }

    public function update(Request $request, JabatanOrganisasi $organisasi)
    {
        $request->validate([
            'nama_jabatan'  => 'required|string|max:150',
            'parent_id'     => 'nullable|exists:jabatan_organisasis,id',
            'tipe_hubungan' => 'required|in:komando,koordinasi,konsultasi',
            'urutan'        => 'nullable|integer|min:0',
            'deskripsi'     => 'nullable|string',
        ]);

        $organisasi->update([
            'nama_jabatan'  => $request->nama_jabatan,
            'parent_id'     => $request->parent_id ?: null,
            'tipe_hubungan' => $request->tipe_hubungan,
            'urutan'        => $request->urutan ?? 0,
            'deskripsi'     => $request->deskripsi,
        ]);

        return redirect()->route('admin.organisasi.index')
            ->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(JabatanOrganisasi $organisasi)
    {
        $organisasi->delete();
        return redirect()->route('admin.organisasi.index')
            ->with('success', 'Jabatan berhasil dihapus.');
    }

    public function anggota(JabatanOrganisasi $organisasi)
    {
        $gurus   = Guru::orderBy('nama')->get();
        $anggota = $organisasi->semuaAnggota()->with('guru')->orderBy('is_aktif', 'desc')->get();

        return view('admin.organisasi.anggota', compact('organisasi', 'gurus', 'anggota'));
    }

    public function anggotaStore(Request $request, JabatanOrganisasi $organisasi)
    {
        $request->validate([
            'nama'    => 'required|string|max:150',
            'guru_id' => 'nullable|exists:gurus,id',
            'foto'    => 'nullable|image|max:1024',
            'nip'     => 'nullable|string|max:50',
            'periode' => 'nullable|string|max:50',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('organisasi', 'public');
        }

        AnggotaOrganisasi::create([
            'jabatan_organisasi_id' => $organisasi->id,
            'nama'                  => $request->nama,
            'guru_id'               => $request->guru_id ?: null,
            'foto'                  => $fotoPath,
            'nip'                   => $request->nip,
            'periode'               => $request->periode,
            'is_aktif'              => true,
        ]);

        return redirect()->route('admin.organisasi.anggota', $organisasi)
            ->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function anggotaDestroy(JabatanOrganisasi $organisasi, AnggotaOrganisasi $anggota)
    {
        if ($anggota->foto) {
            Storage::disk('public')->delete($anggota->foto);
        }
        $anggota->delete();

        return redirect()->route('admin.organisasi.anggota', $organisasi)
            ->with('success', 'Anggota berhasil dihapus.');
    }
}
