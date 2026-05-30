<?php

namespace App\Http\Controllers\OrangTua;

use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Semester;

class DashboardController extends BaseOrangTuaController
{
    public function index()
    {
        $ortu = $this->orangTua();
        $anak = $this->anakAktif($ortu);

        if (! $anak) {
            return view('orangtua.dashboard', ['ortu' => $ortu, 'anak' => null]);
        }

        $semester = Semester::where('aktif', true)->first();

        // Ringkasan absensi semester aktif
        $absensiQuery = Absensi::where('siswa_id', $anak->id);
        $rekapAbsensi = [
            'Hadir' => (clone $absensiQuery)->where('status', 'Hadir')->count(),
            'Izin'  => (clone $absensiQuery)->where('status', 'Izin')->count(),
            'Sakit' => (clone $absensiQuery)->where('status', 'Sakit')->count(),
            'Alpa'  => (clone $absensiQuery)->where('status', 'Alpa')->count(),
        ];

        // SPP belum lunas
        $tagihanBelum = $anak->tagihanSpps()
            ->where('status', '!=', 'lunas')
            ->orderBy('bulan')
            ->get();
        $totalTunggakan = $tagihanBelum->sum(fn($t) => $t->sisa_tagihan ?? ($t->nominal - $t->total_dibayar));

        // Poin pelanggaran & jumlah prestasi
        $totalPoinPelanggaran = $anak->pelanggarans()->sum('poin');
        $jumlahPrestasi = $anak->prestasis()->count();

        // Jumlah mapel
        $jumlahMapel = Jadwal::where('kelas_id', $anak->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->distinct('mapel_id')
            ->count('mapel_id');

        return view('orangtua.dashboard', compact(
            'ortu', 'anak', 'rekapAbsensi', 'tagihanBelum', 'totalTunggakan',
            'totalPoinPelanggaran', 'jumlahPrestasi', 'jumlahMapel', 'semester'
        ));
    }
}
