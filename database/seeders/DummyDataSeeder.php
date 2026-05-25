<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Matkul;
use App\Models\Jadwal;
use App\Models\TahunAjaran;
use App\Models\Semester;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ==================== TAHUN AJARAN & SEMESTER ====================
        $tahunAjaran = TahunAjaran::create(['nama' => '2024/2025', 'aktif' => true]);
        Semester::create(['tahun_ajaran_id' => $tahunAjaran->id, 'semester' => 1, 'aktif' => false]);
        Semester::create(['tahun_ajaran_id' => $tahunAjaran->id, 'semester' => 2, 'aktif' => true]);

        // ==================== ADMIN ====================
        User::create([
            'name'     => 'Administrator',
            'username' => 'admin',
            'email'    => 'admin@smk48.sch.id',
            'password' => Hash::make('password'),
            'role'     => 0,
        ]);

        // ==================== JURUSAN ====================
        $tkj  = Jurusan::create(['nama_jurusan' => 'Teknik Komputer dan Jaringan']);
        $rpl  = Jurusan::create(['nama_jurusan' => 'Rekayasa Perangkat Lunak']);
        $mm   = Jurusan::create(['nama_jurusan' => 'Multimedia']);

        // ==================== GURU ====================
        $userGuru1 = User::create([
            'name'     => 'Siti Delimasari, S.Kom',
            'username' => 'siti.delimasari',
            'email'    => 'siti@smk48.sch.id',
            'password' => Hash::make('password'),
            'role'     => 1,
        ]);
        $guru1 = Guru::create([
            'user_id'      => $userGuru1->id,
            'nip'          => '198501012010012001',
            'nama'         => 'Siti Delimasari, S.Kom',
            'bidang_studi' => 'Basis Data',
            'no_hp'        => '081234567890',
        ]);

        $userGuru2 = User::create([
            'name'     => 'Rahman Manto, S.Kom',
            'username' => 'rahman.manto',
            'email'    => 'rahman@smk48.sch.id',
            'password' => Hash::make('password'),
            'role'     => 1,
        ]);
        $guru2 = Guru::create([
            'user_id'      => $userGuru2->id,
            'nip'          => '198701012012011002',
            'nama'         => 'Rahman Manto, S.Kom',
            'bidang_studi' => 'Pemrograman',
            'no_hp'        => '081234567891',
        ]);

        // Akun guru lama (untuk kompatibilitas, tanpa profil guru)
        User::create([
            'name'     => 'Guru User',
            'username' => 'guru',
            'email'    => 'guru@example.com',
            'password' => Hash::make('password'),
            'role'     => 1,
        ]);

        // ==================== KELAS ====================
        $kelasTkj1  = Kelas::create(['nama_kelas' => 'X TKJ 1',  'jurusan_id' => $tkj->id, 'tingkat' => 'X',   'wali_kelas_id' => $guru1->id]);
        $kelasTkj2  = Kelas::create(['nama_kelas' => 'XI TKJ 1', 'jurusan_id' => $tkj->id, 'tingkat' => 'XI',  'wali_kelas_id' => $guru2->id]);
        $kelasRpl1  = Kelas::create(['nama_kelas' => 'X RPL 1',  'jurusan_id' => $rpl->id, 'tingkat' => 'X',   'wali_kelas_id' => null]);
        Kelas::create(['nama_kelas' => 'X MM 1',   'jurusan_id' => $mm->id,  'tingkat' => 'X',   'wali_kelas_id' => null]);

        // ==================== SISWA ====================
        $userSiswa = User::create([
            'name'     => 'Roi Saputra',
            'username' => 'siswa',
            'email'    => 'siswa@example.com',
            'password' => Hash::make('password'),
            'role'     => 2,
        ]);
        Siswa::create([
            'user_id'       => $userSiswa->id,
            'jurusan_id'    => $tkj->id,
            'kelas_id'      => $kelasTkj1->id,
            'nama'          => 'Roi Saputra',
            'nisn'          => '0012345678',
            'tanggal_lahir' => '2008-01-15',
        ]);

        $userSiswa2 = User::create([
            'name'     => 'Budi Santoso',
            'username' => 'budi.santoso',
            'email'    => 'budi@example.com',
            'password' => Hash::make('password'),
            'role'     => 2,
        ]);
        Siswa::create([
            'user_id'       => $userSiswa2->id,
            'jurusan_id'    => $tkj->id,
            'kelas_id'      => $kelasTkj1->id,
            'nama'          => 'Budi Santoso',
            'nisn'          => '0012345679',
            'tanggal_lahir' => '2008-03-20',
        ]);

        $userSiswa3 = User::create([
            'name'     => 'Ani Rahayu',
            'username' => 'ani.rahayu',
            'email'    => 'ani@example.com',
            'password' => Hash::make('password'),
            'role'     => 2,
        ]);
        Siswa::create([
            'user_id'       => $userSiswa3->id,
            'jurusan_id'    => $rpl->id,
            'kelas_id'      => $kelasRpl1->id,
            'nama'          => 'Ani Rahayu',
            'nisn'          => '0012345680',
            'tanggal_lahir' => '2008-07-10',
        ]);

        $semesterAktif = Semester::where('aktif', true)->first();

        // ==================== MATA PELAJARAN & JADWAL ====================
        $matkul1 = Matkul::create(['kode' => 'BD-44', 'nama_matkul' => 'Basis Data',         'dosen_pengampu' => 'Siti Delimasari, S.Kom']);
        $matkul2 = Matkul::create(['kode' => 'SD-33', 'nama_matkul' => 'Struktur Data',      'dosen_pengampu' => 'Rahman Manto, S.Kom']);
        $matkul3 = Matkul::create(['kode' => 'PW-55', 'nama_matkul' => 'Pemrograman Web',    'dosen_pengampu' => 'Rahman Manto, S.Kom']);
        $matkul4 = Matkul::create(['kode' => 'JK-66', 'nama_matkul' => 'Jaringan Komputer',  'dosen_pengampu' => 'Siti Delimasari, S.Kom']);

        Jadwal::create(['matkul_id' => $matkul1->id, 'kelas_id' => $kelasTkj1->id, 'guru_id' => $guru1->id, 'semester_id' => $semesterAktif->id, 'hari' => 'Selasa',  'jam_mulai' => '08:00', 'jam_selesai' => '09:30', 'ruangan' => 'Lab Komputer 1', 'link_zoom' => 'https://zoom.us/j/123']);
        Jadwal::create(['matkul_id' => $matkul2->id, 'kelas_id' => $kelasTkj1->id, 'guru_id' => $guru2->id, 'semester_id' => $semesterAktif->id, 'hari' => 'Rabu',    'jam_mulai' => '10:00', 'jam_selesai' => '11:30', 'ruangan' => 'Ruang 3', 'link_zoom' => 'https://zoom.us/j/456']);
        Jadwal::create(['matkul_id' => $matkul3->id, 'kelas_id' => $kelasTkj2->id, 'guru_id' => $guru2->id, 'semester_id' => $semesterAktif->id, 'hari' => 'Kamis',   'jam_mulai' => '13:00', 'jam_selesai' => '14:30', 'ruangan' => 'Lab Komputer 2']);
        Jadwal::create(['matkul_id' => $matkul4->id, 'kelas_id' => $kelasTkj1->id, 'guru_id' => $guru1->id, 'semester_id' => $semesterAktif->id, 'hari' => 'Jumat',   'jam_mulai' => '07:30', 'jam_selesai' => '09:00', 'ruangan' => 'Lab Komputer 1']);
        Jadwal::create(['matkul_id' => $matkul1->id, 'kelas_id' => $kelasRpl1->id, 'guru_id' => $guru1->id, 'semester_id' => $semesterAktif->id, 'hari' => 'Senin',   'jam_mulai' => '08:00', 'jam_selesai' => '09:30', 'ruangan' => 'Ruang 5']);
    }
}
