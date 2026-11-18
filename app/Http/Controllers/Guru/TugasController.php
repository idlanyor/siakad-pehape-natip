<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    private function getGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $guru = $this->getGuru();

        $jadwals = Jadwal::with(['mapel', 'kelas', 'semester.tahunAjaran'])
            ->where('guru_id', $guru->id)
            ->withCount('tugas')
            ->urutHari()
            ->orderBy('jam_mulai')
            ->get();

        return view('guru.tugas.index', compact('jadwals'));
    }

    public function show(Jadwal $jadwal)
    {
        $guru = $this->getGuru();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $jadwal->load(['mapel', 'kelas', 'semester.tahunAjaran']);
        $tugas = Tugas::where('jadwal_id', $jadwal->id)
            ->withCount('pengumpulans')
            ->orderBy('deadline')
            ->get();

        return view('guru.tugas.show', compact('jadwal', 'tugas'));
    }

    public function create(Jadwal $jadwal)
    {
        $guru = $this->getGuru();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $jadwal->load(['mapel', 'kelas']);
        return view('guru.tugas.form', compact('jadwal'));
    }

    public function store(Request $request, Jadwal $jadwal)
    {
        $guru = $this->getGuru();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $request->validate([
            'judul'             => 'required|string|max:255',
            'instruksi'         => 'required|string',
            'deadline'          => 'required|date|after:now',
            'nilai_maksimal'    => 'required|integer|min:1|max:100',
            'tipe_pengumpulan'  => 'required|in:file,link,teks',
        ]);

        Tugas::create([
            'jadwal_id'         => $jadwal->id,
            'guru_id'           => $guru->id,
            'judul'             => $request->judul,
            'instruksi'         => $request->instruksi,
            'deadline'          => $request->deadline,
            'nilai_maksimal'    => $request->nilai_maksimal,
            'tipe_pengumpulan'  => $request->tipe_pengumpulan,
        ]);

        return redirect()->route('guru.tugas.show', $jadwal)
            ->with('success', 'Tugas berhasil dibuat.');
    }

    public function edit(Tugas $tugas)
    {
        $guru = $this->getGuru();
        abort_if($tugas->guru_id !== $guru->id, 403);

        $tugas->load(['jadwal.mapel', 'jadwal.kelas']);
        return view('guru.tugas.form', ['jadwal' => $tugas->jadwal, 'tugas' => $tugas]);
    }

    public function update(Request $request, Tugas $tugas)
    {
        $guru = $this->getGuru();
        abort_if($tugas->guru_id !== $guru->id, 403);

        $request->validate([
            'judul'             => 'required|string|max:255',
            'instruksi'         => 'required|string',
            'deadline'          => 'required|date',
            'nilai_maksimal'    => 'required|integer|min:1|max:100',
            'tipe_pengumpulan'  => 'required|in:file,link,teks',
        ]);

        $tugas->update($request->only('judul', 'instruksi', 'deadline', 'nilai_maksimal', 'tipe_pengumpulan'));

        return redirect()->route('guru.tugas.show', $tugas->jadwal_id)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Tugas $tugas)
    {
        $guru = $this->getGuru();
        abort_if($tugas->guru_id !== $guru->id, 403);

        // Hapus file pengumpulan jika ada
        foreach ($tugas->pengumpulans as $p) {
            if ($tugas->tipe_pengumpulan === 'file' && $p->konten) {
                Storage::disk('public')->delete($p->konten);
            }
        }

        $jadwalId = $tugas->jadwal_id;
        $tugas->delete();

        return redirect()->route('guru.tugas.show', $jadwalId)
            ->with('success', 'Tugas berhasil dihapus.');
    }

    public function pengumpulan(Tugas $tugas)
    {
        $guru = $this->getGuru();
        abort_if($tugas->guru_id !== $guru->id, 403);

        $tugas->load(['jadwal.mapel', 'jadwal.kelas', 'jadwal.semester.tahunAjaran']);

        $pengumpulans = PengumpulanTugas::with('siswa')
            ->where('tugas_id', $tugas->id)
            ->orderBy('created_at')
            ->get()
            ->keyBy('siswa_id');

        // Daftar semua siswa di kelas ini
        $siswas = \App\Models\Siswa::where('kelas_id', $tugas->jadwal->kelas_id)
            ->orderBy('nama')
            ->get();

        return view('guru.tugas.pengumpulan', compact('tugas', 'pengumpulans', 'siswas'));
    }

    public function beriNilai(Request $request, PengumpulanTugas $pengumpulan)
    {
        $guru = $this->getGuru();
        abort_if($pengumpulan->tugas->guru_id !== $guru->id, 403);

        $request->validate([
            'nilai'    => 'required|numeric|min:0|max:' . $pengumpulan->tugas->nilai_maksimal,
            'feedback' => 'nullable|string|max:1000',
        ]);

        $pengumpulan->update([
            'nilai'       => $request->nilai,
            'feedback'    => $request->feedback,
            'dinilai_at'  => now(),
            'dinilai_oleh' => auth()->id(),
        ]);

        return back()->with('success', "Nilai untuk {$pengumpulan->siswa->nama} berhasil disimpan.");
    }
}
