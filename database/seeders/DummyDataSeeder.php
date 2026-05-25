<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Jurusan;
use App\Models\Matkul;
use App\Models\Jadwal;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jurusan = Jurusan::create(['nama_jurusan' => 'Teknik Komputer dan Jaringan']);

        $user = User::create([
            'name' => 'Siswa User',
            'username' => 'siswa',
            'email' => 'siswa@example.com',
            'password' => Hash::make('password'),
            'role' => 2,
        ]);

        Siswa::create([
            'user_id' => $user->id,
            'jurusan_id' => $jurusan->id,
            'nama' => 'Roi',
            'nisn' => '12345678',
            'tanggal_lahir' => '2008-01-01',
        ]);

        User::create([
            'name' => 'Guru User',
            'username' => 'guru',
            'email' => 'guru@example.com',
            'password' => Hash::make('password'),
            'role' => 1,
        ]);

        $matkul1 = Matkul::create([
            'kode' => '#44',
            'nama_matkul' => 'Basis Data',
            'dosen_pengampu' => 'Siti Delimasari,S.Kom',
        ]);

        Jadwal::create([
            'matkul_id' => $matkul1->id,
            'hari' => 'Selasa',
            'waktu' => '18:30 - 19:30',
            'link_zoom' => 'https://zoom.us/j/123',
        ]);

        $matkul2 = Matkul::create([
            'kode' => '#33',
            'nama_matkul' => 'Struktur Data',
            'dosen_pengampu' => 'Rahmam Manto,S.Kom',
        ]);

        Jadwal::create([
            'matkul_id' => $matkul2->id,
            'hari' => 'Rabu',
            'waktu' => '19:30 - 20:30',
            'link_zoom' => 'https://zoom.us/j/456',
        ]);
    }
}
