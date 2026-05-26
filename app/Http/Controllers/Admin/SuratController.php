<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\SuratKeterangan;
use Illuminate\Http\Request;

class SuratController extends Controller
{
    public function index(Request $request)
    {
        $query = SuratKeterangan::with(['siswa', 'pencatat'])
            ->orderBy('tanggal_surat', 'desc');

        if ($request->filled('jenis_surat')) {
            $query->where('jenis_surat', $request->jenis_surat);
        }

        $surats = $query->get();

        return view('admin.kesiswaan.surat.index', compact('surats'));
    }

    public function create()
    {
        $siswas = Siswa::with(['kelas', 'jurusan'])->orderBy('nama')->get();
        $jenisList = SuratKeterangan::JENIS_LABELS;
        return view('admin.kesiswaan.surat.form', ['surat' => null, 'siswas' => $siswas, 'jenisList' => $jenisList]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id'      => 'required|exists:siswas,id',
            'jenis_surat'   => 'required|in:aktif,pindah,lulus',
            'nomor_surat'   => 'required|string|max:100',
            'tanggal_surat' => 'required|date',
            'keterangan'    => 'nullable|string',
        ]);

        $surat = SuratKeterangan::create([
            'siswa_id'      => $request->siswa_id,
            'jenis_surat'   => $request->jenis_surat,
            'nomor_surat'   => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'keterangan'    => $request->keterangan,
            'dicatat_oleh'  => auth()->id(),
        ]);

        return redirect()->route('admin.surat.print', $surat)
            ->with('success', 'Surat keterangan berhasil dibuat.');
    }

    public function edit(SuratKeterangan $surat)
    {
        $siswas = Siswa::with(['kelas', 'jurusan'])->orderBy('nama')->get();
        $jenisList = SuratKeterangan::JENIS_LABELS;
        return view('admin.kesiswaan.surat.form', compact('surat', 'siswas', 'jenisList'));
    }

    public function update(Request $request, SuratKeterangan $surat)
    {
        $request->validate([
            'siswa_id'      => 'required|exists:siswas,id',
            'jenis_surat'   => 'required|in:aktif,pindah,lulus',
            'nomor_surat'   => 'required|string|max:100',
            'tanggal_surat' => 'required|date',
            'keterangan'    => 'nullable|string',
        ]);

        $surat->update($request->only(['siswa_id', 'jenis_surat', 'nomor_surat', 'tanggal_surat', 'keterangan']));

        return redirect()->route('admin.surat.index')
            ->with('success', 'Surat keterangan berhasil diperbarui.');
    }

    public function destroy(SuratKeterangan $surat)
    {
        $surat->delete();
        return redirect()->route('admin.surat.index')
            ->with('success', 'Surat keterangan berhasil dihapus.');
    }

    public function print(SuratKeterangan $surat)
    {
        $surat->load(['siswa.kelas.jurusan', 'siswa.jurusan', 'pencatat']);
        return view('admin.kesiswaan.surat.print', compact('surat'));
    }
}
