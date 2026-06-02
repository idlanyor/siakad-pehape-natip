<?php

namespace Database\Seeders;

use App\Models\Siswa;
use App\Models\UserSiswa;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siswaData = [
            [
                'siakad_id' => 1,
                'nis' => '12345',
                'nisn' => '1234567890',
                'nama_lengkap' => 'Ahmad Rizki',
                'kelas_id' => 1,
                'kelas_nama' => 'X RPL 1',
                'tingkat' => 10,
                'username' => '12345',
                'email' => 'ahmad.rizki@example.com',
                'status' => 'aktif',
            ],
            [
                'siakad_id' => 2,
                'nis' => '12346',
                'nisn' => '1234567891',
                'nama_lengkap' => 'Siti Nurhaliza',
                'kelas_id' => 1,
                'kelas_nama' => 'X RPL 1',
                'tingkat' => 10,
                'username' => '12346',
                'email' => 'siti.nurhaliza@example.com',
                'status' => 'aktif',
            ],
            [
                'siakad_id' => 3,
                'nis' => '12347',
                'nisn' => '1234567892',
                'nama_lengkap' => 'Budi Santoso',
                'kelas_id' => 2,
                'kelas_nama' => 'X RPL 2',
                'tingkat' => 10,
                'username' => '12347',
                'email' => 'budi.santoso@example.com',
                'status' => 'aktif',
            ],
            [
                'siakad_id' => 4,
                'nis' => '12348',
                'nisn' => '1234567893',
                'nama_lengkap' => 'Dewi Lestari',
                'kelas_id' => 2,
                'kelas_nama' => 'X RPL 2',
                'tingkat' => 10,
                'username' => '12348',
                'email' => 'dewi.lestari@example.com',
                'status' => 'aktif',
            ],
            [
                'siakad_id' => 5,
                'nis' => '12349',
                'nisn' => '1234567894',
                'nama_lengkap' => 'Eko Prasetyo',
                'kelas_id' => 3,
                'kelas_nama' => 'XI RPL 1',
                'tingkat' => 11,
                'username' => '12349',
                'email' => 'eko.prasetyo@example.com',
                'status' => 'aktif',
            ],
        ];

        foreach ($siswaData as $data) {
            $siswa = Siswa::create($data);
            
            // Create user_siswa account with password = NIS
            UserSiswa::createOrUpdateFromSiswa($siswa);
            
            $this->command->info("Created siswa: {$siswa->nama_lengkap} (NIS: {$siswa->nis})");
        }

        $this->command->info('');
        $this->command->info('✓ Seeder completed successfully!');
        $this->command->info('');
        $this->command->info('Test accounts created:');
        $this->command->info('Username: 12345 | Password: 12345 | Name: Ahmad Rizki');
        $this->command->info('Username: 12346 | Password: 12346 | Name: Siti Nurhaliza');
        $this->command->info('Username: 12347 | Password: 12347 | Name: Budi Santoso');
        $this->command->info('Username: 12348 | Password: 12348 | Name: Dewi Lestari');
        $this->command->info('Username: 12349 | Password: 12349 | Name: Eko Prasetyo');
        $this->command->info('');
        $this->command->info('Login at: http://64.235.45.179:8413/siswa/login');
    }
}
