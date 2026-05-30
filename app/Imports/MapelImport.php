<?php

namespace App\Imports;

use App\Models\Jurusan;
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

            // Resolve jurusan_id dari nama jurusan (opsional)
            $jurusanId = null;
            $jurusanNama = trim($row['jurusan'] ?? '');
            if ($jurusanNama) {
                $jurusan = Jurusan::where('nama_jurusan', $jurusanNama)->first();
                $jurusanId = $jurusan?->id;
            }

            $existing = Mapel::where('kode', $kode)->first();
            if ($existing) {
                $existing->update([
                    'nama_mapel'  => $nama,
                    'jurusan_id'  => $jurusanId ?? $existing->jurusan_id,
                ]);
                $this->updated++;
            } else {
                Mapel::create([
                    'kode'        => $kode,
                    'nama_mapel'  => $nama,
                    'jurusan_id'  => $jurusanId,
                ]);
                $this->imported++;
            }
        }
    }
}
