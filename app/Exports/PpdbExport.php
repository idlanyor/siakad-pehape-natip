<?php

namespace App\Exports;

use Illuminate\Support\Enumerable;
use App\Models\PendaftarPpdb;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PpdbExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private string $status = 'all') {}

    public function title(): string
    {
        return 'Data PPDB ' . ucfirst($this->status);
    }

    public function headings(): array
    {
        return [
            'No. Pendaftaran', 'Nama', 'JK', 'Tempat Lahir', 'Tanggal Lahir',
            'NISN', 'Asal Sekolah', 'No. HP', 'Email',
            'Jurusan Pilihan 1', 'Jurusan Pilihan 2', 'Nilai Rata-rata',
            'Status', 'Kelas Diterima', 'Gelombang', 'Tahun Ajaran',
            'Akun Username', 'Tanggal Daftar',
        ];
    }

    public function collection(): Enumerable
    {
        $query = PendaftarPpdb::with(['jurusanPilihan', 'jurusanPilihan2', 'kelasDiterima', 'gelombangPpdb']);

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->orderBy('no_pendaftaran')->get()->map(fn($p) => [
            $p->no_pendaftaran,
            $p->nama,
            $p->jk_label,
            $p->tempat_lahir,
            $p->tanggal_lahir?->format('d/m/Y'),
            $p->nisn ?? '-',
            $p->asal_sekolah,
            $p->no_hp,
            $p->email ?? '-',
            $p->jurusanPilihan?->nama_jurusan ?? '-',
            $p->jurusanPilihan2?->nama_jurusan ?? '-',
            $p->nilai_rata_rata ?? '-',
            $p->status_label,
            $p->kelasDiterima?->nama_kelas ?? '-',
            $p->gelombangPpdb?->nama ?? $p->gelombang,
            $p->tahun_ajaran,
            $p->akun_username ?? '-',
            $p->created_at->format('d/m/Y'),
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

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 28, 'C' => 5, 'D' => 16, 'E' => 14,
            'F' => 14, 'G' => 25, 'H' => 14, 'I' => 25,
            'J' => 25, 'K' => 25, 'L' => 8,
            'M' => 12, 'N' => 16, 'O' => 14, 'P' => 12,
            'Q' => 18, 'R' => 14,
        ];
    }
}
