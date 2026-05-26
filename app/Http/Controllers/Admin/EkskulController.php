<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnggotaEkskul;
use App\Models\Ekskul;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class EkskulController extends Controller
{
    public function index()
    {
        $ekskuls = Ekskul::with(['pembina', 'anggotas'])->orderBy('nama')->get();
        return view('admin.kesiswaan.ekskul.index', compact('ekskuls'));
    }

    public function create()
    {
        $gurus = Guru::orderBy('nama')->get();
        return view('admin.kesiswaan.ekskul.form', ['ekskul' => null, 'gurus' => $gurus]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'       => 'required|string|max:100|unique:ekskuls,nama',
            'pembina_id' => 'nullable|exists:gurus,id',
            'deskripsi'  => 'nullable|string',
        ]);

        Ekskul::create($request->only(['nama', 'pembina_id', 'deskripsi']));

        return redirect()->route('admin.ekskul.index')
            ->with('success', 'Ekskul berhasil ditambahkan.');
    }

    public function edit(Ekskul $ekskul)
    {
        $gurus = Guru::orderBy('nama')->get();
        return view('admin.kesiswaan.ekskul.form', compact('ekskul', 'gurus'));
    }

    public function update(Request $request, Ekskul $ekskul)
    {
        $request->validate([
            'nama'       => 'required|string|max:100|unique:ekskuls,nama,' . $ekskul->id,
            'pembina_id' => 'nullable|exists:gurus,id',
            'deskripsi'  => 'nullable|string',
        ]);

        $ekskul->update($request->only(['nama', 'pembina_id', 'deskripsi']));

        return redirect()->route('admin.ekskul.index')
            ->with('success', 'Ekskul berhasil diperbarui.');
    }

    public function destroy(Ekskul $ekskul)
    {
        $ekskul->delete();
        return redirect()->route('admin.ekskul.index')
            ->with('success', 'Ekskul berhasil dihapus.');
    }

    public function anggota(Ekskul $ekskul)
    {
        $tahunAjaran = TahunAjaran::aktif();
        $anggotas = AnggotaEkskul::with('siswa')
            ->where('ekskul_id', $ekskul->id)
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->get();

        // Siswa yang belum terdaftar di ekskul ini pada tahun ajaran aktif
        $anggotaSiswaIds = $anggotas->pluck('siswa_id');
        $siswas = Siswa::with(['kelas', 'jurusan'])
            ->whereNotIn('id', $anggotaSiswaIds)
            ->orderBy('nama')
            ->get();

        return view('admin.kesiswaan.ekskul.anggota', compact('ekskul', 'anggotas', 'siswas', 'tahunAjaran'));
    }

    public function anggotaStore(Request $request, Ekskul $ekskul)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
        ]);

        $tahunAjaran = TahunAjaran::aktif();
        if (!$tahunAjaran) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif. Aktifkan tahun ajaran terlebih dahulu.');
        }

        AnggotaEkskul::firstOrCreate([
            'ekskul_id'       => $ekskul->id,
            'siswa_id'        => $request->siswa_id,
            'tahun_ajaran_id' => $tahunAjaran->id,
        ]);

        return back()->with('success', 'Siswa berhasil ditambahkan ke ekskul.');
    }

    public function anggotaDestroy(Ekskul $ekskul, AnggotaEkskul $anggota)
    {
        $anggota->delete();
        return back()->with('success', 'Siswa berhasil dikeluarkan dari ekskul.');
    }
}
