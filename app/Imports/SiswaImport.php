<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;
    public array $skipped = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $baris = $i + 2;
            $nisn  = trim($row['nisn'] ?? $row['nis'] ?? '');
            $nama  = trim($row['nama_lengkap'] ?? $row['nama'] ?? '');

            if (!$nama) {
                $this->skipped[] = "Baris {$baris}: kolom nama kosong.";
                continue;
            }

            if ($nisn && Siswa::where('nisn', $nisn)->exists()) {
                $this->skipped[] = "Baris {$baris}: NISN {$nisn} sudah ada, dilewati.";
                continue;
            }

            $username = $nisn ?: 'siswa' . time() . $i;
            if (User::where('username', $username)->exists()) {
                $this->skipped[] = "Baris {$baris}: Username {$username} sudah ada, dilewati.";
                continue;
            }

            // Cocokkan kelas berdasarkan nama_kelas
            $namaKelas = trim($row['kelas'] ?? '');
            $kelas     = $namaKelas ? Kelas::where('nama_kelas', $namaKelas)->first() : null;

            $user = User::create([
                'name'     => $nama,
                'username' => $username,
                'email'    => trim($row['email'] ?? '') ?: null,
                'password' => Hash::make($nisn ?: 'password'),
                'role'     => 2,
            ]);

            Siswa::create([
                'user_id'       => $user->id,
                'kelas_id'      => $kelas?->id,
                'jurusan_id'    => $kelas?->jurusan_id,
                'nama'          => $nama,
                'nisn'          => $nisn ?: null,
                'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
            ]);

            $this->imported++;
        }
    }

    private function parseDate($val): ?string
    {
        if (!$val) return null;
        if (is_numeric($val)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $val)->format('Y-m-d');
        }
        try {
            return \Carbon\Carbon::parse($val)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
