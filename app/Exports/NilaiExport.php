<?php

namespace App\Exports;

use App\Models\Jadwal;
use App\Models\Nilai;
use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NilaiExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private Jadwal $jadwal) {}

    public function title(): string
    {
        return 'Nilai ' . ($this->jadwal->mapel->nama_mapel ?? 'Mapel');
    }

    public function headings(): array
    {
        return [
            'No', 'Nama Siswa', 'NISN',
            'Tugas', 'PTS', 'PAS',
            'Praktik', 'Proyek', 'Portofolio',
            'Rata Pengetahuan', 'Rata Keterampilan',
            'Nilai Akhir', 'Predikat',
        ];
    }

    public function collection()
    {
        $siswas = Siswa::where('kelas_id', $this->jadwal->kelas_id)->orderBy('nama')->get();
        $nilais = Nilai::where('jadwal_id', $this->jadwal->id)->get()->keyBy('siswa_id');

        return $siswas->map(function ($s, $i) use ($nilais) {
            $n = $nilais->get($s->id);
            return [
                $i + 1,
                $s->nama,
                $s->nisn ?? '-',
                $n?->tugas,
                $n?->pts,
                $n?->pas,
                $n?->praktik,
                $n?->proyek,
                $n?->portofolio,
                $n?->rata_pengetahuan,
                $n?->rata_keterampilan,
                $n?->nilai_akhir,
                $n?->predikat ?? '-',
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1F497D']], 'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 28, 'C' => 15, 'D' => 8, 'E' => 8, 'F' => 8, 'G' => 8, 'H' => 8, 'I' => 12, 'J' => 18, 'K' => 18, 'L' => 12, 'M' => 10];
    }
}
