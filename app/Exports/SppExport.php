<?php

namespace App\Exports;

use App\Models\TagihanSpp;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SppExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private Collection $tagihans) {}

    public function title(): string { return 'Data SPP'; }

    public function headings(): array
    {
        return ['No', 'Nama Siswa', 'Kelas', 'Bulan', 'Nominal', 'Dibayar', 'Sisa', 'Status'];
    }

    public function collection()
    {
        return $this->tagihans->values()->map(function ($t, $i) {
            return [
                $i + 1,
                $t->siswa->nama,
                $t->siswa->kelas?->nama_kelas ?? '-',
                $t->bulan_label,
                (float) $t->nominal,
                round($t->total_dibayar),
                round($t->sisa_tagihan),
                $t->status_label,
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1F497D']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 28, 'C' => 12, 'D' => 14, 'E' => 14, 'F' => 14, 'G' => 14, 'H' => 12];
    }
}
