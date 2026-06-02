<?php

namespace Tests\Unit;

use App\Models\{PeriodeUjian, Ujian, SesiUjian, SoalUjian, JawabanSiswa};
use App\Support\SesiPresenter;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SesiPresenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_builds_soals_progress_and_remaining_time(): void
    {
        $periode = PeriodeUjian::create([
            'nama' => 'Ganjil 2026/2027',
            'tahun_ajaran' => '2026/2027',
            'semester' => 'ganjil',
            'status' => 'aktif',
        ]);
        $ujian = Ujian::create([
            'periode_id' => $periode->id, 'judul' => 'UH1', 'tipe' => 'quiz',
            'kelas_id' => 7, 'waktu_mulai' => now()->subMinute(),
            'waktu_selesai' => now()->addHour(), 'durasi_menit' => 60,
            'passing_grade' => 70, 'status' => 'published', 'is_published' => true,
        ]);
        $soal = SoalUjian::create([
            'ujian_id' => $ujian->id, 'nomor' => 1, 'tipe' => 'pilihan_ganda',
            'pertanyaan' => '2+2?', 'opsi_a' => '3', 'opsi_b' => '4',
            'kunci_jawaban' => 'B', 'bobot_nilai' => 1,
        ]);
        $sesi = SesiUjian::create([
            'ujian_id' => $ujian->id, 'siswa_id' => 1, 'siswa_nama' => 'Budi',
            'kelas_id' => 7, 'status' => 'berlangsung', 'is_resmi' => true,
            'waktu_mulai' => now()->subMinute(), 'durasi_detik' => 3600,
        ]);
        JawabanSiswa::create([
            'sesi_ujian_id' => $sesi->id, 'soal_ujian_id' => $soal->id,
            'jawaban' => 'B', 'is_ragu' => false,
        ]);

        $payload = (new SesiPresenter())->forPengerjaan($sesi->fresh());

        $this->assertCount(1, $payload['soals']);
        $this->assertSame('2+2?', $payload['soals'][0]['pertanyaan']);
        $this->assertNull($payload['soals'][0]['kunci_jawaban']); // disembunyikan saat berlangsung
        $this->assertSame(1, $payload['progress']['terjawab']);
        $this->assertGreaterThan(0, $payload['sisa_waktu_detik']);
    }
}
