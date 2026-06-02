<?php

namespace App\Http\Controllers\Admin;

use App\Exports\HasilUjianExport;
use App\Http\Controllers\Controller;
use App\Models\Ujian;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class HasilUjianController extends Controller
{
    public function exportExcel(Ujian $ujian)
    {
        $ujian->load(['jadwal.mapel', 'jadwal.kelas']);

        $filename = 'Hasil_Ujian_'
            . str_replace(' ', '_', $ujian->judul)
            . '_' . ($ujian->jadwal->kelas->nama_kelas ?? '')
            . '.xlsx';

        return Excel::download(new HasilUjianExport($ujian), $filename);
    }

    public function exportPdf(Ujian $ujian)
    {
        $ujian->load(['jadwal.mapel', 'jadwal.kelas', 'guru']);

        // Ambil semua sesi
        $sesis = $ujian->sesiUjians()
            ->with('siswa')
            ->whereIn('status', ['selesai', 'timeout', 'diskualifikasi'])
            ->orderBy('nilai_akhir', 'desc')
            ->get();

        // Statistik
        $sesisSelesai = $sesis->whereIn('status', ['selesai', 'timeout'])->whereNotNull('nilai_akhir');
        $stats = [
            'total_peserta' => $ujian->jumlah_peserta,
            'sudah_mengerjakan' => $sesisSelesai->count(),
            'rata_rata' => $sesisSelesai->avg('nilai_akhir') ?? 0,
            'tertinggi' => $sesisSelesai->max('nilai_akhir') ?? 0,
            'terendah' => $sesisSelesai->min('nilai_akhir') ?? 0,
            'lulus' => $sesisSelesai->where('nilai_akhir', '>=', $ujian->passing_grade)->count(),
            'passing_rate' => $sesisSelesai->count() > 0
                ? round(($sesisSelesai->where('nilai_akhir', '>=', $ujian->passing_grade)->count() / $sesisSelesai->count()) * 100, 2)
                : 0,
        ];

        $pdf = Pdf::loadView('admin.ujian.hasil-pdf', compact('ujian', 'sesis', 'stats'))
            ->setPaper('a4', 'portrait');

        $filename = 'Hasil_Ujian_' . str_replace(' ', '_', $ujian->judul) . '.pdf';

        return $pdf->download($filename);
    }
}
