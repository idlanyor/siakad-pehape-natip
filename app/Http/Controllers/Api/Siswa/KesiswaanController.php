<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AnggotaEkskul;
use App\Models\Pelanggaran;
use App\Models\Prestasi;
use App\Models\Siswa;

class KesiswaanController extends Controller
{
    public function index()
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();

        $pelanggarans = Pelanggaran::where('siswa_id', $siswa->id)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(fn($p) => [
                'id'        => $p->id,
                'tanggal'   => $p->tanggal,
                'jenis'     => $p->jenis,
                'deskripsi' => $p->deskripsi,
                'poin'      => $p->poin,
            ]);

        $prestasis = Prestasi::where('siswa_id', $siswa->id)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(fn($p) => [
                'id'        => $p->id,
                'tanggal'   => $p->tanggal,
                'nama'      => $p->nama_prestasi,
                'tingkat'   => $p->tingkat,
                'keterangan'=> $p->keterangan,
            ]);

        $ekskuls = AnggotaEkskul::with('ekskul')
            ->where('siswa_id', $siswa->id)
            ->get()
            ->map(fn($a) => [
                'id'   => $a->ekskul->id,
                'nama' => $a->ekskul->nama,
            ]);

        return response()->json([
            'pelanggarans'  => $pelanggarans,
            'total_poin'    => $pelanggarans->sum('poin'),
            'prestasis'     => $prestasis,
            'ekskuls'       => $ekskuls,
        ]);
    }
}
