<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AbsensiEkskul;
use App\Models\AnggotaEkskul;
use App\Models\Siswa;
use App\Models\TahunAjaran;

class KesiswaanController extends Controller
{
    public function index()
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        $tahunAjaran = TahunAjaran::aktif();

        // Pelanggaran
        $pelanggarans = $siswa->pelanggarans()
            ->orderBy('tanggal', 'desc')
            ->get();
        $totalPoin = $pelanggarans->sum('poin');

        // Prestasi
        $prestasis = $siswa->prestasis()
            ->orderBy('tanggal', 'desc')
            ->get();

        // Ekskul + absensi
        $anggotaEkskuls = AnggotaEkskul::with('ekskul.pembina')
            ->where('siswa_id', $siswa->id)
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->get();

        $ekskulStats = $anggotaEkskuls->map(function ($anggota) use ($siswa) {
            $totalPertemuan = AbsensiEkskul::where('ekskul_id', $anggota->ekskul_id)
                ->distinct('tanggal')
                ->count('tanggal');

            $jumlahHadir = AbsensiEkskul::where('ekskul_id', $anggota->ekskul_id)
                ->where('siswa_id', $siswa->id)
                ->where('status', 'Hadir')
                ->count();

            return [
                'ekskul'          => $anggota->ekskul,
                'total_pertemuan' => $totalPertemuan,
                'jumlah_hadir'    => $jumlahHadir,
                'persen_hadir'    => $totalPertemuan > 0 ? round(($jumlahHadir / $totalPertemuan) * 100) : 0,
            ];
        });

        return view('siswa.kesiswaan.index', compact(
            'siswa', 'pelanggarans', 'totalPoin', 'prestasis', 'ekskulStats', 'tahunAjaran'
        ));
    }
}
