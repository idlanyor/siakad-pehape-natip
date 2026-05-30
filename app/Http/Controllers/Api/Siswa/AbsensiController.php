<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Resources\AbsensiResource;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Semester;
use App\Models\Siswa;

class AbsensiController extends Controller
{
    public function index()
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $jadwalIds = Jadwal::where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->pluck('id');

        // Return summary per jadwal + detail records
        $absensis = Absensi::with(['jadwal.mapel'])
            ->where('siswa_id', $siswa->id)
            ->whereIn('jadwal_id', $jadwalIds)
            ->orderBy('tanggal', 'desc')
            ->get();

        $rekap = $absensis->groupBy('jadwal_id')->map(function ($items) {
            $total = $items->count();
            $hadir = $items->where('status', 'Hadir')->count();
            return [
                'mapel'    => $items->first()->jadwal->mapel->nama_mapel ?? null,
                'total'    => $total,
                'hadir'    => $hadir,
                'izin'     => $items->where('status', 'Izin')->count(),
                'sakit'    => $items->where('status', 'Sakit')->count(),
                'alpa'     => $items->where('status', 'Alpa')->count(),
                'persen'   => $total > 0 ? round(($hadir / $total) * 100) : 0,
            ];
        });

        return response()->json([
            'rekap'   => $rekap->values(),
            'records' => AbsensiResource::collection($absensis->take(50)),
        ]);
    }
}
