<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    public function show(Jadwal $jadwal)
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)->orderBy('nama')->get(['id', 'nama', 'nisn']);

        $existing = Nilai::where('jadwal_id', $jadwal->id)
            ->get()
            ->keyBy('siswa_id');

        $data = $siswas->map(fn($s) => [
            'siswa_id'   => $s->id,
            'nama'       => $s->nama,
            'nisn'       => $s->nisn,
            'tugas'      => $existing[$s->id]?->tugas,
            'pts'        => $existing[$s->id]?->pts,
            'pas'        => $existing[$s->id]?->pas,
            'praktik'    => $existing[$s->id]?->praktik,
            'proyek'     => $existing[$s->id]?->proyek,
            'portofolio' => $existing[$s->id]?->portofolio,
            'catatan'    => $existing[$s->id]?->catatan,
        ]);

        return response()->json([
            'jadwal' => [
                'id'    => $jadwal->id,
                'mapel' => $jadwal->mapel->nama_mapel ?? null,
                'kelas' => $jadwal->kelas->nama_kelas,
            ],
            'siswa' => $data,
        ]);
    }

    public function store(Request $request, Jadwal $jadwal)
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $request->validate([
            'nilai'              => 'required|array',
            'nilai.*.siswa_id'   => 'required|integer|exists:siswas,id',
            'nilai.*.tugas'      => 'nullable|numeric|min:0|max:100',
            'nilai.*.pts'        => 'nullable|numeric|min:0|max:100',
            'nilai.*.pas'        => 'nullable|numeric|min:0|max:100',
            'nilai.*.praktik'    => 'nullable|numeric|min:0|max:100',
            'nilai.*.proyek'     => 'nullable|numeric|min:0|max:100',
            'nilai.*.portofolio' => 'nullable|numeric|min:0|max:100',
            'nilai.*.catatan'    => 'nullable|string|max:500',
        ]);

        foreach ($request->nilai as $item) {
            Nilai::updateOrCreate(
                ['jadwal_id' => $jadwal->id, 'siswa_id' => $item['siswa_id']],
                [
                    'tugas'        => $item['tugas'] ?? null,
                    'pts'          => $item['pts'] ?? null,
                    'pas'          => $item['pas'] ?? null,
                    'praktik'      => $item['praktik'] ?? null,
                    'proyek'       => $item['proyek'] ?? null,
                    'portofolio'   => $item['portofolio'] ?? null,
                    'catatan'      => $item['catatan'] ?? null,
                    'dicatat_oleh' => auth()->id(),
                ]
            );
        }

        return response()->json(['message' => 'Nilai berhasil disimpan.']);
    }
}
