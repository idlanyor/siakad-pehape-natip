<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/** Daftar akun hasil impor beserta password awal, untuk dibagikan ke pemilik akun. */
class AkunImportExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function __construct(private array $akun) {}

    public function headings(): array
    {
        return ['Nama', 'Keterangan', 'Username', 'Password awal'];
    }

    public function array(): array
    {
        return array_map(fn ($a) => [$a['nama'], $a['keterangan'] ?? '', $a['username'], $a['password']], $this->akun);
    }
}
