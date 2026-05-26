<?php

namespace App\Exports\Templates;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportTemplateExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        private string $title,
        private array  $headings,
        private array  $sample,
        private array  $widths,
    ) {}

    public function title(): string { return $this->title; }
    public function headings(): array { return $this->headings; }
    public function array(): array { return $this->sample; }
    public function columnWidths(): array { return $this->widths; }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1F497D']],
            ],
        ];
    }
}
