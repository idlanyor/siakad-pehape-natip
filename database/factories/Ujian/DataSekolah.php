<?php

namespace Database\Factories\Ujian;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Pembuat data master SIAKAD minimal untuk factory dan test modul Ujian.
 * Model SIAKAD belum punya factory, jadi data dibuat langsung di sini.
 */
class DataSekolah
{
    public static function jurusan(): Jurusan
    {
        return Jurusan::create(['nama_jurusan' => 'Jurusan '.Str::upper(Str::random(4))]);
    }

    public static function kelas(?Jurusan $jurusan = null): Kelas
    {
        $jurusan ??= self::jurusan();

        return Kelas::create([
            'nama_kelas' => 'X '.Str::upper(Str::random(3)),
            'jurusan_id' => $jurusan->id,
            'tingkat' => 'X',
        ]);
    }

    public static function mapel(?Jurusan $jurusan = null): Mapel
    {
        $jurusan ??= self::jurusan();

        return Mapel::create([
            'kode' => 'M'.Str::upper(Str::random(6)),
            'nama_mapel' => 'Mapel '.Str::random(5),
            'jurusan_id' => $jurusan->id,
        ]);
    }

    public static function semester(): Semester
    {
        $tahunAjaran = TahunAjaran::create(['nama' => '2026/2027', 'aktif' => true]);

        return Semester::create(['tahun_ajaran_id' => $tahunAjaran->id, 'semester' => 1, 'aktif' => true]);
    }

    public static function guru(?User $user = null): Guru
    {
        $user ??= User::factory()->create(['role' => 1]);

        return Guru::create([
            'user_id' => $user->id,
            'nip' => (string) fake()->unique()->numerify('19##########'),
            'nama' => $user->name,
        ]);
    }

    public static function siswa(?Kelas $kelas = null, ?User $user = null): Siswa
    {
        $kelas ??= self::kelas();
        $user ??= User::factory()->create(['role' => 2]);

        return Siswa::create([
            'user_id' => $user->id,
            'jurusan_id' => $kelas->jurusan_id,
            'kelas_id' => $kelas->id,
            'nama' => $user->name,
            'nisn' => (string) fake()->unique()->numerify('00########'),
            'tanggal_lahir' => '2010-01-01',
        ]);
    }
}
