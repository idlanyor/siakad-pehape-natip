<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Models\Ujian;
use App\Models\PeriodeUjian;
use App\Models\SoalUjian;
use App\Models\SesiUjian;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UjianCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $periode;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test periode and store reference
        $this->periode = PeriodeUjian::create([
            'nama' => 'UTS Semester 1',
            'tahun_ajaran' => '2024/2025',
            'semester' => 1,
            'tanggal_mulai' => now()->subDays(5),
            'tanggal_selesai' => now()->addDays(30),
            'is_active' => true,
        ]);
    }

    #[Test]
    public function guru_can_create_ujian()
    {
        $response = $this->authenticatedRequest('POST', '/api/ujian', [
            'periode_id' => $this->periode->id,
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'deskripsi' => 'Ujian bab 1-5',
            'waktu_mulai' => now()->addDays(1)->toDateTimeString(),
            'waktu_selesai' => now()->addDays(1)->addHours(2)->toDateTimeString(),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 30,
            'acak_soal' => true,
            'acak_opsi' => true,
            'tampilkan_nilai' => true,
        ], [
            'user_id' => 5,
            'nama' => 'Pak Budi',
            'role' => 'guru',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Ujian berhasil dibuat',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'periode_id',
                    'guru_id',
                    'guru_nama',
                    'judul',
                    'is_published',
                ],
            ]);

        $this->assertDatabaseHas('ujian', [
            'judul' => 'Ujian Matematika',
            'guru_id' => 5,
            'is_published' => false,
        ]);
    }

    #[Test]
    public function siswa_cannot_create_ujian()
    {
        $response = $this->authenticatedRequest('POST', '/api/ujian', [
            'periode_id' => $this->periode->id,
            'judul' => 'Ujian Test',
        ], [
            'user_id' => 10,
            'nama' => 'Ahmad',
            'role' => 'siswa',
        ]);

        // Should be rejected by authorization logic
        $response->assertStatus(403);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_ujian()
    {
        $response = $this->authenticatedRequest('POST', '/api/ujian', [], [
            'user_id' => 5,
            'nama' => 'Pak Budi',
            'role' => 'guru',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'periode_id',
                'kelas_id',
                'mapel_id',
                'judul',
                'waktu_mulai',
                'waktu_selesai',
                'durasi_menit',
                'passing_grade',
                'jumlah_soal',
            ]);
    }

    #[Test]
    public function guru_can_view_their_ujian()
    {
        $ujian = Ujian::create([
            'periode_id' => $this->periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'waktu_mulai' => now()->addDays(1),
            'waktu_selesai' => now()->addDays(1)->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 30,
        ]);

        $response = $this->authenticatedRequest('GET', "/api/ujian/{$ujian->id}", [], [
            'user_id' => 5,
            'nama' => 'Pak Budi',
            'role' => 'guru',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $ujian->id,
                    'judul' => 'Ujian Matematika',
                ],
            ]);
    }

    #[Test]
    public function guru_cannot_view_other_guru_ujian()
    {
        $ujian = Ujian::create([
            'periode_id' => $this->periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'waktu_mulai' => now()->addDays(1),
            'waktu_selesai' => now()->addDays(1)->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 30,
        ]);

        $response = $this->authenticatedRequest('GET', "/api/ujian/{$ujian->id}", [], [
            'user_id' => 6,
            'nama' => 'Pak Ahmad',
            'role' => 'guru',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_view_any_ujian()
    {
        $ujian = Ujian::create([
            'periode_id' => $this->periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'waktu_mulai' => now()->addDays(1),
            'waktu_selesai' => now()->addDays(1)->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 30,
        ]);

        $response = $this->authenticatedRequest('GET', "/api/ujian/{$ujian->id}", [], [
            'user_id' => 1,
            'nama' => 'Admin',
            'role' => 'admin',
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function guru_can_update_their_ujian()
    {
        $ujian = Ujian::create([
            'periode_id' => $this->periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'waktu_mulai' => now()->addDays(1),
            'waktu_selesai' => now()->addDays(1)->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 30,
        ]);

        $response = $this->authenticatedRequest('PUT', "/api/ujian/{$ujian->id}", [
            'judul' => 'Ujian Matematika Updated',
            'passing_grade' => 80,
        ], [
            'user_id' => 5,
            'nama' => 'Pak Budi',
            'role' => 'guru',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ujian berhasil diupdate',
            ]);

        $this->assertDatabaseHas('ujian', [
            'id' => $ujian->id,
            'judul' => 'Ujian Matematika Updated',
            'passing_grade' => 80,
        ]);
    }

    #[Test]
    public function cannot_update_ujian_that_has_sessions()
    {
        $ujian = Ujian::create([
            'periode_id' => $this->periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'waktu_mulai' => now()->addDays(1),
            'waktu_selesai' => now()->addDays(1)->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 30,
        ]);

        // Create a session
        SesiUjian::create([
            'ujian_id' => $ujian->id,
            'siswa_id' => 10,
            'siswa_nama' => 'Ahmad',
            'kelas_id' => 10,
            'waktu_mulai' => now(),
            'durasi_detik' => 5400,
            'status' => 'berlangsung',
            'is_resmi' => true,
        ]);

        $response = $this->authenticatedRequest('PUT', "/api/ujian/{$ujian->id}", [
            'judul' => 'Updated Title',
        ], [
            'user_id' => 5,
            'nama' => 'Pak Budi',
            'role' => 'guru',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Tidak dapat mengubah ujian yang sudah memiliki sesi',
            ]);
    }

    #[Test]
    public function guru_can_delete_their_ujian()
    {
        $ujian = Ujian::create([
            'periode_id' => $this->periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'waktu_mulai' => now()->addDays(1),
            'waktu_selesai' => now()->addDays(1)->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 30,
        ]);

        $response = $this->authenticatedRequest('DELETE', "/api/ujian/{$ujian->id}", [], [
            'user_id' => 5,
            'nama' => 'Pak Budi',
            'role' => 'guru',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ujian berhasil dihapus',
            ]);

        $this->assertSoftDeleted('ujian', [
            'id' => $ujian->id,
        ]);
    }

    #[Test]
    public function cannot_delete_ujian_that_has_sessions()
    {
        $ujian = Ujian::create([
            'periode_id' => $this->periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'waktu_mulai' => now()->addDays(1),
            'waktu_selesai' => now()->addDays(1)->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 30,
        ]);

        SesiUjian::create([
            'ujian_id' => $ujian->id,
            'siswa_id' => 10,
            'siswa_nama' => 'Ahmad',
            'kelas_id' => 10,
            'waktu_mulai' => now(),
            'durasi_detik' => 5400,
            'status' => 'selesai',
            'is_resmi' => true,
        ]);

        $response = $this->authenticatedRequest('DELETE', "/api/ujian/{$ujian->id}", [], [
            'user_id' => 5,
            'nama' => 'Pak Budi',
            'role' => 'guru',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Tidak dapat menghapus ujian yang sudah memiliki sesi',
            ]);
    }

    #[Test]
    public function guru_can_publish_ujian_with_sufficient_soals()
    {
        $ujian = Ujian::create([
            'periode_id' => $this->periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'waktu_mulai' => now()->addDays(1),
            'waktu_selesai' => now()->addDays(1)->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 2,
            'is_published' => false,
        ]);

        // Create soals using factory for consistency
        SoalUjian::factory()->create([
            'ujian_id' => $ujian->id,
            'nomor' => 1,
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Soal 1',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'kunci_jawaban' => 'A',
            'bobot_nilai' => 1,
        ]);

        SoalUjian::factory()->create([
            'ujian_id' => $ujian->id,
            'nomor' => 2,
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Soal 2',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'kunci_jawaban' => 'B',
            'bobot_nilai' => 1,
        ]);

        $response = $this->authenticatedRequest('POST', "/api/ujian/{$ujian->id}/publish", [], [
            'user_id' => 5,
            'nama' => 'Pak Budi',
            'role' => 'guru',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ujian berhasil dipublish',
            ]);

        $this->assertDatabaseHas('ujian', [
            'id' => $ujian->id,
            'is_published' => true,
        ]);
    }

    #[Test]
    public function cannot_publish_ujian_without_sufficient_soals()
    {
        $ujian = Ujian::create([
            'periode_id' => $this->periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'waktu_mulai' => now()->addDays(1),
            'waktu_selesai' => now()->addDays(1)->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 30,
            'is_published' => false,
        ]);

        $response = $this->authenticatedRequest('POST', "/api/ujian/{$ujian->id}/publish", [], [
            'user_id' => 5,
            'nama' => 'Pak Budi',
            'role' => 'guru',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    #[Test]
    public function guru_can_unpublish_ujian()
    {
        $ujian = Ujian::create([
            'periode_id' => $this->periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'waktu_mulai' => now()->addDays(1),
            'waktu_selesai' => now()->addDays(1)->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 30,
            'is_published' => true,
        ]);

        $response = $this->authenticatedRequest('POST', "/api/ujian/{$ujian->id}/unpublish", [], [
            'user_id' => 5,
            'nama' => 'Pak Budi',
            'role' => 'guru',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ujian berhasil di-unpublish',
            ]);

        $this->assertDatabaseHas('ujian', [
            'id' => $ujian->id,
            'is_published' => false,
        ]);
    }

    #[Test]
    public function it_can_list_ujian_with_filters()
    {
        // Create multiple ujian
        Ujian::create([
            'periode_id' => $this->periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'waktu_mulai' => now()->addDays(1),
            'waktu_selesai' => now()->addDays(1)->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 30,
        ]);

        Ujian::create([
            'periode_id' => $this->periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 11,
            'mapel_id' => 6,
            'judul' => 'Ujian Fisika',
            'waktu_mulai' => now()->addDays(2),
            'waktu_selesai' => now()->addDays(2)->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 30,
        ]);

        $response = $this->authenticatedRequest('GET', '/api/ujian?kelas_id=10', [], [
            'user_id' => 5,
            'nama' => 'Pak Budi',
            'role' => 'guru',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data.data');
    }
}