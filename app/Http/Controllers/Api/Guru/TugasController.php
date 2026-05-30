<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Http\Resources\PengumpulanTugasResource;
use App\Http\Resources\TugasResource;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use Illuminate\Http\Request;

class TugasController extends Controller
{
    private function getGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * Tugas pada satu jadwal milik guru.
     */
    public function show(Jadwal $jadwal)
    {
        $guru = $this->getGuru();
        abort_if($jadwal->guru_id !== $guru->id, 403, 'Akses ditolak.');

        $tugas = Tugas::with('jadwal.mapel')
            ->where('jadwal_id', $jadwal->id)
            ->withCount('pengumpulans')
            ->orderBy('deadline')
            ->get();

        return TugasResource::collection($tugas);
    }

    public function store(Request $request, Jadwal $jadwal)
    {
        $guru = $this->getGuru();
        abort_if($jadwal->guru_id !== $guru->id, 403, 'Akses ditolak.');

        $request->validate([
            'judul'            => 'required|string|max:255',
            'instruksi'        => 'required|string',
            'deadline'         => 'required|date|after:now',
            'nilai_maksimal'   => 'required|integer|min:1|max:100',
            'tipe_pengumpulan' => 'required|in:file,link,teks',
        ]);

        $tugas = Tugas::create([
            'jadwal_id'        => $jadwal->id,
            'guru_id'          => $guru->id,
            'judul'            => $request->judul,
            'instruksi'        => $request->instruksi,
            'deadline'         => $request->deadline,
            'nilai_maksimal'   => $request->nilai_maksimal,
            'tipe_pengumpulan' => $request->tipe_pengumpulan,
        ]);
        $tugas->load('jadwal.mapel');

        return (new TugasResource($tugas))
            ->additional(['message' => 'Tugas berhasil dibuat.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Daftar pengumpulan siswa untuk satu tugas.
     */
    public function pengumpulan(Tugas $tugas)
    {
        $guru = $this->getGuru();
        abort_if($tugas->guru_id !== $guru->id, 403, 'Akses ditolak.');

        $pengumpulans = PengumpulanTugas::with('siswa')
            ->where('tugas_id', $tugas->id)
            ->orderBy('created_at')
            ->get();

        return PengumpulanTugasResource::collection($pengumpulans);
    }

    public function beriNilai(Request $request, PengumpulanTugas $pengumpulan)
    {
        $guru = $this->getGuru();
        abort_if($pengumpulan->tugas->guru_id !== $guru->id, 403, 'Akses ditolak.');

        $request->validate([
            'nilai'    => 'required|numeric|min:0|max:' . $pengumpulan->tugas->nilai_maksimal,
            'feedback' => 'nullable|string|max:1000',
        ]);

        $pengumpulan->update([
            'nilai'        => $request->nilai,
            'feedback'     => $request->feedback,
            'dinilai_at'   => now(),
            'dinilai_oleh' => auth()->id(),
        ]);

        return (new PengumpulanTugasResource($pengumpulan->fresh('siswa')))
            ->additional(['message' => 'Nilai berhasil disimpan.']);
    }
}
