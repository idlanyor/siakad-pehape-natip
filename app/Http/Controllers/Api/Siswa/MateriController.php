<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Resources\MateriResource;
use App\Models\Jadwal;
use App\Models\Materi;
use App\Models\Semester;
use App\Models\Siswa;

class MateriController extends Controller
{
    private function getSiswa(): Siswa
    {
        return Siswa::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * Daftar materi terbit untuk kelas siswa, dikelompokkan per jadwal/mapel.
     */
    public function index()
    {
        $siswa    = $this->getSiswa();
        $semester = Semester::aktif();

        $jadwalIds = Jadwal::where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn ($q) => $q->where('semester_id', $semester->id))
            ->pluck('id');

        $materis = Materi::with(['jadwal.mapel', 'guru'])
            ->whereIn('jadwal_id', $jadwalIds)
            ->where('is_published', true)
            ->orderByDesc('created_at')
            ->get();

        return MateriResource::collection($materis);
    }

    public function show(Materi $materi)
    {
        $siswa = $this->getSiswa();
        // Hanya materi terbit milik kelas siswa.
        abort_if($materi->jadwal->kelas_id !== $siswa->kelas_id, 403, 'Akses ditolak.');
        abort_if(! $materi->is_published, 404);

        $materi->load(['jadwal.mapel', 'guru']);

        return new MateriResource($materi);
    }
}
