<?php

namespace App\Http\Controllers\OrangTua;

use App\Models\Jadwal;
use App\Models\Nilai;
use App\Models\Semester;

class NilaiController extends BaseOrangTuaController
{
    public function index()
    {
        $ortu = $this->orangTua();
        $anak = $this->anakAktif($ortu);
        abort_if(! $anak, 404, 'Belum ada data anak.');

        $semester = Semester::aktif();

        $jadwals = Jadwal::with(['mapel', 'guru'])
            ->where('kelas_id', $anak->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->get();

        $nilais = Nilai::where('siswa_id', $anak->id)
            ->whereIn('jadwal_id', $jadwals->pluck('id'))
            ->get()
            ->keyBy('jadwal_id');

        return view('orangtua.nilai', compact('ortu', 'anak', 'jadwals', 'nilais', 'semester'));
    }
}
