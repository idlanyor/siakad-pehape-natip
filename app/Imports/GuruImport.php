<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use App\Support\PasswordAwal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuruImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    public array $skipped = [];

    /** Akun yang dibuat beserta password awalnya, untuk diunduh admin sekali. */
    public array $akun = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $baris = $i + 2; // +2 karena heading row
            $nip = trim($row['nip'] ?? '');
            $nama = trim($row['nama_lengkap'] ?? $row['nama'] ?? '');

            if (! $nama) {
                $this->skipped[] = "Baris {$baris}: kolom nama kosong.";

                continue;
            }

            if ($nip && Guru::where('nip', $nip)->exists()) {
                $this->skipped[] = "Baris {$baris}: NIP {$nip} sudah ada, dilewati.";

                continue;
            }

            $username = $nip ?: 'guru'.time().$i;
            if (User::where('username', $username)->exists()) {
                $this->skipped[] = "Baris {$baris}: Username {$username} sudah ada, dilewati.";

                continue;
            }

            $user = User::create([
                'name' => $nama,
                'username' => $username,
                'email' => trim($row['email'] ?? '') ?: null,
                'password' => Hash::make($password = PasswordAwal::buat()), // dulu = NIP atau 'password'
                'role' => 1,
            ]);

            Guru::create([
                'user_id' => $user->id,
                'nip' => $nip ?: null,
                'nama' => $nama,
                'bidang_studi' => trim($row['bidang_studi'] ?? '') ?: null,
                'no_hp' => trim($row['no_hp'] ?? '') ?: null,
            ]);

            $this->akun[] = ['nama' => $nama, 'keterangan' => $nip ? "NIP {$nip}" : '', 'username' => $username, 'password' => $password];
            $this->imported++;
        }
    }
}
