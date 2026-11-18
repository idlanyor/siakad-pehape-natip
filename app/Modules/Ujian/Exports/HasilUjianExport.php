<?php

namespace App\Modules\Ujian\Exports;

use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\Ujian;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HasilUjianExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  Collection<int, SesiUjian>  $sesi
     */
    public function __construct(private readonly Ujian $ujian, private readonly Collection $sesi) {}

    public function title(): string
    {
        return mb_substr('Hasil '.$this->ujian->judul, 0, 31);
    }

    public function headings(): array
    {
        return [
            'No', 'Nama Siswa', 'NISN', 'Status', 'Resmi', 'Waktu Mulai', 'Waktu Selesai',
            'Nilai PG', 'Nilai Essay', 'Nilai Akhir', 'Predikat', 'Lulus', 'Pelanggaran',
        ];
    }

    public function collection(): Collection
    {
        $kkm = (float) $this->ujian->passing_grade;

        return $this->sesi->values()->map(fn (SesiUjian $s, int $i) => [
            $i + 1,
            $s->siswa?->nama ?? '-',
            $s->siswa?->nisn ?? '-',
            ucfirst($s->status),
            $s->is_resmi ? 'Ya' : 'Try out',
            $s->waktu_mulai?->format('d/m/Y H:i') ?? '-',
            $s->waktu_selesai?->format('d/m/Y H:i') ?? '-',
            $s->nilai_otomatis ?? '-',
            $s->nilai_manual ?? '-',
            $s->nilai_akhir ?? '-',
            $s->predikat ?? '-',
            $s->nilai_akhir === null ? '-' : ((float) $s->nilai_akhir >= $kkm ? 'Ya' : 'Tidak'),
            $s->jumlah_pelanggaran,
        ]);
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
}
