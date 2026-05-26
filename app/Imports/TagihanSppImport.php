<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\TagihanSpp;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TagihanSppImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public int $updated  = 0;
    public array $skipped = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $baris  = $i + 2;
            $nisn   = trim($row['nisn'] ?? $row['nis'] ?? '');
            $bulan  = trim($row['bulan'] ?? '');
            $nominal = (float) ($row['nominal'] ?? 0);

            if (!$nisn) {
                $this->skipped[] = "Baris {$baris}: NISN kosong.";
                continue;
            }

            $siswa = Siswa::where('nisn', $nisn)->first();
            if (!$siswa) {
                $this->skipped[] = "Baris {$baris}: Siswa NISN {$nisn} tidak ditemukan.";
                continue;
            }

            if (!$bulan || !$nominal) {
                $this->skipped[] = "Baris {$baris}: bulan atau nominal kosong.";
                continue;
            }

            // Parse bulan: terima YYYY-MM atau YYYY-MM-DD
            $parsedBulan = $this->parseBulan($bulan);
            if (!$parsedBulan) {
                $this->skipped[] = "Baris {$baris}: format bulan '{$bulan}' tidak valid (gunakan YYYY-MM).";
                continue;
            }

            $existing = TagihanSpp::where('siswa_id', $siswa->id)
                ->whereYear('bulan', substr($parsedBulan, 0, 4))
                ->whereMonth('bulan', substr($parsedBulan, 5, 2))
                ->first();

            if ($existing) {
                // Jangan update nominal jika sudah ada pembayaran — bisa bikin inkonsisten
                if ($existing->pembayarans()->exists()) {
                    $this->skipped[] = "Baris {$baris}: Tagihan NISN {$nisn} bulan {$parsedBulan} sudah ada pembayaran, nominal tidak diubah.";
                    continue;
                }
                $existing->update([
                    'nominal'    => $nominal,
                    'keterangan' => trim($row['keterangan'] ?? '') ?: $existing->keterangan,
                ]);
                $this->updated++;
            } else {
                TagihanSpp::create([
                    'siswa_id'   => $siswa->id,
                    'bulan'      => $parsedBulan . '-01',
                    'nominal'    => $nominal,
                    'status'     => 'belum',
                    'keterangan' => trim($row['keterangan'] ?? '') ?: null,
                ]);
                $this->imported++;
            }
        }
    }

    private function parseBulan(string $val): ?string
    {
        if (preg_match('/^\d{4}-\d{2}$/', $val)) return $val;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) return substr($val, 0, 7);
        return null;
    }
}
