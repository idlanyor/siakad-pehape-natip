<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Nilai;
use App\Models\Semester;
use App\Models\Siswa;

class RaportController extends Controller
{
    public function index()
    {
        $siswa = Siswa::with(['kelas', 'jurusan'])->where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $jadwals = Jadwal::with('mapel')
            ->where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->get();

        $nilais = Nilai::where('siswa_id', $siswa->id)
            ->whereIn('jadwal_id', $jadwals->pluck('id'))
            ->get()
            ->keyBy('jadwal_id');

        $data = $jadwals->map(function ($jadwal) use ($nilais) {
            $nilai = $nilais->get($jadwal->id);
            return [
                'mapel'             => $jadwal->mapel->nama_mapel ?? null,
                'rata_pengetahuan'  => $nilai?->rata_pengetahuan,
                'rata_keterampilan' => $nilai?->rata_keterampilan,
                'nilai_akhir'       => $nilai?->nilai_akhir,
                'predikat'          => $nilai?->predikat ?? '-',
                'catatan'           => $nilai?->catatan,
            ];
        });

        return response()->json([
            'siswa' => [
                'nama'  => $siswa->nama,
                'nisn'  => $siswa->nisn,
                'kelas' => $siswa->kelas?->nama_kelas,
            ],
            'semester' => $semester ? [
                'id'       => $semester->id,
                'semester' => $semester->semester,
            ] : null,
            'nilai' => $data,
        ]);
    }
}
