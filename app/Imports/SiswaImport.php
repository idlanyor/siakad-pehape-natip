<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Support\PasswordAwal;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SiswaImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    public array $skipped = [];

    /** Akun yang dibuat beserta password awalnya, untuk diunduh admin sekali. */
    public array $akun = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $baris = $i + 2;
            $nisn = trim($row['nisn'] ?? $row['nis'] ?? '');
            $nama = trim($row['nama_lengkap'] ?? $row['nama'] ?? '');

            if (! $nama) {
                $this->skipped[] = "Baris {$baris}: kolom nama kosong.";

                continue;
            }

            // NISN wajib & unik di tabel siswas; dulu baris tanpa NISN menggagalkan seluruh impor.
            if (! $nisn) {
                $this->skipped[] = "Baris {$baris}: kolom NISN kosong, dilewati.";

                continue;
            }

            if (Siswa::where('nisn', $nisn)->exists()) {
                $this->skipped[] = "Baris {$baris}: NISN {$nisn} sudah ada, dilewati.";

                continue;
            }

            $username = $nisn ?: 'siswa'.time().$i;
            if (User::where('username', $username)->exists()) {
                $this->skipped[] = "Baris {$baris}: Username {$username} sudah ada, dilewati.";

                continue;
            }

            // Cocokkan kelas berdasarkan nama_kelas
            $namaKelas = trim($row['kelas'] ?? '');
            $kelas = $namaKelas ? Kelas::where('nama_kelas', $namaKelas)->first() : null;

            // Siswa wajib punya jurusan (diambil dari kelas). Dulu baris tanpa kelas yang cocok
            // memicu error NOT NULL dan menggagalkan seluruh impor.
            if (! $kelas) {
                $this->skipped[] = $namaKelas
                    ? "Baris {$baris}: kelas \"{$namaKelas}\" tidak ditemukan, dilewati."
                    : "Baris {$baris}: kolom kelas kosong, dilewati.";

                continue;
            }

            // Dulu password = NISN (atau 'password'), padahal NISN bukan rahasia.
            $password = PasswordAwal::buat();
            $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null);
            if (! $tanggalLahir) {
                $this->skipped[] = "Baris {$baris}: tanggal lahir kosong atau formatnya tidak dikenali, dilewati.";

                continue;
            }

            $user = User::create([
                'name' => $nama,
                'username' => $username,
                'email' => trim($row['email'] ?? '') ?: null,
                'password' => Hash::make($password),
                'role' => 2,
            ]);

            Siswa::create([
                'user_id' => $user->id,
                'kelas_id' => $kelas?->id,
                'jurusan_id' => $kelas?->jurusan_id,
                'nama' => $nama,
                'nisn' => $nisn ?: null,
                'tanggal_lahir' => $tanggalLahir,
            ]);

            $this->akun[] = ['nama' => $nama, 'keterangan' => $kelas?->nama_kelas ?? '', 'username' => $username, 'password' => $password];
            $this->imported++;
        }
    }

    private function parseDate($val): ?string
    {
        if (! $val) {
            return null;
        }
        if (is_numeric($val)) {
            return Date::excelToDateTimeObject((int) $val)->format('Y-m-d');
        }
        try {
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
