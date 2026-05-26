<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiExport implements FromArray, WithTitle, WithStyles
{
    public function __construct(private Jadwal $jadwal) {}

    public function title(): string
    {
        return 'Absensi';
    }

    public function array(): array
    {
        $siswas = Siswa::where('kelas_id', $this->jadwal->kelas_id)->orderBy('nama')->get();

        $pertemuan = Absensi::where('jadwal_id', $this->jadwal->id)
            ->distinct()->orderBy('tanggal')->pluck('tanggal');

        $pivot = Absensi::where('jadwal_id', $this->jadwal->id)
            ->get()->groupBy('siswa_id')
            ->map(fn($rows) => $rows->pluck('status', 'tanggal'));

        // Header row
        $header = array_merge(['No', 'Nama Siswa'], $pertemuan->map(fn($t) => \Carbon\Carbon::parse($t)->format('d/m'))->toArray(), ['Hadir', 'Sakit', 'Izin', 'Alpa']);

        $rows = [$header];

        foreach ($siswas as $i => $s) {
            $abs = $pivot->get($s->id, collect());
            $row = [$i + 1, $s->nama];
            foreach ($pertemuan as $tgl) {
                $row[] = $abs->get($tgl, '-');
            }
            $row[] = $abs->filter(fn($v) => $v === 'Hadir')->count();
            $row[] = $abs->filter(fn($v) => $v === 'Sakit')->count();
            $row[] = $abs->filter(fn($v) => $v === 'Izin')->count();
            $row[] = $abs->filter(fn($v) => $v === 'Alpa')->count();
            $rows[] = $row;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1F497D']]],
        ];
    }
}
