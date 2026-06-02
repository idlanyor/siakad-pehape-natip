<?php

namespace App\Exports;

use App\Models\Ujian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HasilUjianExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private Ujian $ujian) {}

    public function title(): string
    {
        return 'Hasil ' . substr($this->ujian->judul, 0, 25);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'NIS',
            'Kelas',
            'Waktu Mulai',
            'Waktu Selesai',
            'Durasi (menit)',
            'Status',
            'Nilai Otomatis',
            'Nilai Manual',
            'Nilai Akhir',
            'Predikat',
            'Tab Switch',
        ];
    }

    public function collection()
    {
        $sesis = $this->ujian->sesiUjians()
            ->with('siswa.kelas')
            ->orderBy('nilai_akhir', 'desc')
            ->get();

        return $sesis->map(function ($sesi, $i) {
            return [
                $i + 1,
                $sesi->siswa->nama ?? '-',
                $sesi->siswa->nis ?? '-',
                $sesi->siswa->kelas->nama_kelas ?? '-',
                $sesi->waktu_mulai?->format('d/m/Y H:i') ?? '-',
                $sesi->waktu_selesai?->format('d/m/Y H:i') ?? '-',
                $sesi->durasi_detik ? round($sesi->durasi_detik / 60, 1) : '-',
                $sesi->status_label,
                $sesi->nilai_otomatis ?? '-',
                $sesi->nilai_manual ?? '-',
                $sesi->nilai_akhir ?? '-',
                $sesi->predikat,
                $sesi->tab_switch_count ?? 0,
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1F497D']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 25,
            'C' => 12,
            'D' => 12,
            'E' => 16,
            'F' => 16,
            'G' => 14,
            'H' => 18,
            'I' => 14,
            'J' => 12,
            'K' => 12,
            'L' => 12,
            'M' => 10,
        ];
    }
}
