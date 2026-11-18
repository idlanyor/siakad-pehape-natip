<?php

namespace Tests\Feature\Ujian\Concerns;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use App\Modules\Ujian\Models\PeriodeUjian;
use App\Modules\Ujian\Models\Ujian;
use Database\Factories\Ujian\DataSekolah;

/**
 * Fixture umum: admin, guru pengampu satu mapel, guru lain, kelas, siswa,
 * periode aktif, dan ujian draft milik guru.
 */
trait SiapkanSekolah
{
    protected User $admin;

    protected Guru $guru;

    protected Guru $guruLain;

    protected Mapel $mapel;

    protected Kelas $kelas;

    protected Siswa $siswa;

    protected PeriodeUjian $periode;

    protected Ujian $ujian;

    protected function siapkanSekolah(): void
    {
        $this->withoutVite();

        $this->admin = User::factory()->create(['role' => 0]);
        $this->guru = DataSekolah::guru();
        $this->guruLain = DataSekolah::guru();
        $this->kelas = DataSekolah::kelas();
        $this->mapel = DataSekolah::mapel();
        $this->guru->mapels()->attach($this->mapel);
        $this->siswa = DataSekolah::siswa($this->kelas);
        $this->periode = PeriodeUjian::factory()->create();
        $this->ujian = Ujian::factory()->create([
            'periode_id' => $this->periode->id,
            'mapel_id' => $this->mapel->id,
            'kelas_id' => $this->kelas->id,
            'guru_id' => $this->guru->id,
        ]);
    }

    protected function dataUjian(array $timpa = []): array
    {
        return [
            'periode_id' => $this->periode->id,
            'judul' => 'UAS Pemrograman',
            'tipe' => 'uas',
            'mapel_id' => $this->mapel->id,
            'kelas_id' => $this->kelas->id,
            'waktu_mulai' => now()->addDay()->format('Y-m-d H:i'),
            'waktu_selesai' => now()->addDay()->addHours(2)->format('Y-m-d H:i'),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'acak_soal' => true,
            'acak_opsi' => false,
            'tampilkan_nilai' => true,
            ...$timpa,
        ];
    }

    protected function dataSoalPg(array $timpa = []): array
    {
        return [
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Apa kepanjangan HTML?',
            'opsi_a' => 'HyperText Markup Language',
            'opsi_b' => 'High Tech Modern Language',
            'opsi_c' => '',
            'kunci_jawaban' => 'a',
            'bobot' => 2,
            ...$timpa,
        ];
    }
}
