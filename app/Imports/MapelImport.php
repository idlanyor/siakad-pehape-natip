<?php

namespace App\Imports;

use App\Models\Mapel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MapelImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public int $updated  = 0;
    public array $skipped = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $baris = $i + 2;
            $kode  = trim($row['kode_mapel'] ?? $row['kode'] ?? '');
            $nama  = trim($row['nama_mapel'] ?? $row['nama'] ?? '');

            if (!$kode || !$nama) {
                $this->skipped[] = "Baris {$baris}: kode atau nama mapel kosong.";
                continue;
            }

            $existing = Mapel::where('kode', $kode)->first();
            if ($existing) {
                $existing->update([
                    'nama_mapel'     => $nama,
                    'dosen_pengampu' => trim($row['deskripsi'] ?? $row['dosen_pengampu'] ?? '') ?: $existing->dosen_pengampu,
                ]);
                $this->updated++;
            } else {
                Mapel::create([
                    'kode'           => $kode,
                    'nama_mapel'     => $nama,
                    'dosen_pengampu' => trim($row['deskripsi'] ?? $row['dosen_pengampu'] ?? '') ?: null,
                ]);
                $this->imported++;
            }
        }
    }
}
