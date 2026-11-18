<?php

namespace Tests\Feature\Api;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Feature\Helpers\ApiTestHelper;
use App\Models\PeriodeUjian;
use App\Models\Ujian;
use App\Models\Soal;
use App\Models\SesiUjian;
use App\Models\JawabanSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SesiUjianTest extends TestCase
{
    use RefreshDatabase;

    protected $siswa;
    protected $guru;
    protected $admin;
    protected $periode;
    protected $ujian;
    protected $soal;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->siswa = ApiTestHelper::createUser('siswa');
        $this->guru = ApiTestHelper::createUser('guru');
        $this->admin = ApiTestHelper::createUser('admin');
        
        $this->periode = PeriodeUjian::create(
            ApiTestHelper::periodeUjianData()
        );

        $this->ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'status' => 'published',
            'tanggal_mulai' => now()->subHour(),
            'tanggal_selesai' => now()->addHour(),
            'durasi' => 90,
        ]);

        $this->soal = Soal::factory()->count(10)->create([
            'ujian_id' => $this->ujian->id,
        ]);
    }

    #[Test]
    public function siswa_can_start_ujian()
    {
        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->postJson('/api/siswa/ujian/' . $this->ujian->id . '/start');

        ApiTestHelper::assertApiSuccess($response, 201);
        $response->assertJsonStructure([
            'data' => [
                'sesi_id',
                'token_akses',
                'waktu_mulai',
                'waktu_selesai_maksimal',
                'soal',
            ],
        ]);

        $this->assertDatabaseHas('sesi_ujian', [
            'ujian_id' => $this->ujian->id,
            'siswa_id' => $this->siswa->id,
            'status' => 'berlangsung',
        ]);
    }

    #[Test]
    public function siswa_cannot_start_ujian_twice()
    {
        // Start first time
        $headers = ApiTestHelper::authHeaders($this->siswa);
        $this->withHeaders($headers)
            ->postJson('/api/siswa/ujian/' . $this->ujian->id . '/start');

        // Try to start again
        $response = $this->withHeaders($headers)
            ->postJson('/api/siswa/ujian/' . $this->ujian->id . '/start');

        ApiTestHelper::assertApiError($response, 422);
    }

    #[Test]
    public function siswa_cannot_start_unpublished_ujian()
    {
        $draftUjian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'status' => 'draft',
        ]);

        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->postJson('/api/siswa/ujian/' . $draftUjian->id . '/start');

        ApiTestHelper::assertApiError($response, 422);
    }

    #[Test]
    public function siswa_cannot_start_ujian_before_start_time()
    {
        $futureUjian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'status' => 'published',
            'tanggal_mulai' => now()->addDays(1),
            'tanggal_selesai' => now()->addDays(2),
        ]);

        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->postJson('/api/siswa/ujian/' . $futureUjian->id . '/start');

        ApiTestHelper::assertApiError($response, 422);
    }

    #[Test]
    public function siswa_cannot_start_ujian_after_end_time()
    {
        $pastUjian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'status' => 'published',
            'tanggal_mulai' => now()->subDays(2),
            'tanggal_selesai' => now()->subDays(1),
        ]);

        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->postJson('/api/siswa/ujian/' . $pastUjian->id . '/start');

        ApiTestHelper::assertApiError($response, 422);
    }

    #[Test]
    public function siswa_can_get_active_sesi()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->getJson('/api/siswa/sesi/' . $sesi->id);

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'id' => $sesi->id,
                'status' => 'sedang_mengerjakan',
            ],
        ]);
    }

    #[Test]
    public function siswa_cannot_access_others_sesi()
    {
        $otherSiswa = ApiTestHelper::createUser('siswa');
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $otherSiswa->id)
        );

        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->getJson('/api/siswa/sesi/' . $sesi->id);

        ApiTestHelper::assertForbidden($response);
    }

    #[Test]
    public function siswa_can_submit_jawaban()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
            ])
        );

        $soal = $this->soal->first();
        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->postJson('/api/siswa/sesi/' . $sesi->id . '/jawaban', [
                'soal_id' => $soal->id,
                'jawaban' => 'A',
            ]);

        ApiTestHelper::assertApiSuccess($response);
        $this->assertDatabaseHas('jawaban_siswa', [
            'sesi_ujian_id' => $sesi->id,
            'soal_id' => $soal->id,
            'jawaban' => 'A',
        ]);
    }

    #[Test]
    public function siswa_can_update_jawaban()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
            ])
        );

        $soal = $this->soal->first();
        
        // Submit first answer
        JawabanSiswa::create([
            'sesi_ujian_id' => $sesi->id,
            'soal_id' => $soal->id,
            'jawaban' => 'A',
        ]);

        $headers = ApiTestHelper::authHeaders($this->siswa);

        // Update answer
        $response = $this->withHeaders($headers)
            ->postJson('/api/siswa/sesi/' . $sesi->id . '/jawaban', [
                'soal_id' => $soal->id,
                'jawaban' => 'B',
            ]);

        ApiTestHelper::assertApiSuccess($response);
        $this->assertDatabaseHas('jawaban_siswa', [
            'sesi_ujian_id' => $sesi->id,
            'soal_id' => $soal->id,
            'jawaban' => 'B',
        ]);
    }

    #[Test]
    public function siswa_cannot_submit_jawaban_after_time_limit()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now()->subMinutes(100), // Exceeded 90 min limit
            ])
        );

        $soal = $this->soal->first();
        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->postJson('/api/siswa/sesi/' . $sesi->id . '/jawaban', [
                'soal_id' => $soal->id,
                'jawaban' => 'A',
            ]);

        ApiTestHelper::assertApiError($response, 422);
    }

    #[Test]
    public function siswa_can_finish_ujian()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now()->subMinutes(30),
            ])
        );

        // Submit some answers
        foreach ($this->soal->take(5) as $soal) {
            JawabanSiswa::create([
                'sesi_ujian_id' => $sesi->id,
                'soal_id' => $soal->id,
                'jawaban' => 'A',
            ]);
        }

        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->postJson('/api/siswa/sesi/' . $sesi->id . '/finish');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'sesi_id',
                'status',
                'nilai',
                'waktu_selesai',
            ],
        ]);

        $this->assertDatabaseHas('sesi_ujian', [
            'id' => $sesi->id,
            'status' => 'selesai',
        ]);
    }

    #[Test]
    public function system_auto_finishes_ujian_after_time_limit()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now()->subMinutes(100), // Exceeded limit
            ])
        );

        // Trigger auto-finish (normally done by scheduled job)
        $this->artisan('ujian:auto-finish');

        $this->assertDatabaseHas('sesi_ujian', [
            'id' => $sesi->id,
            'status' => 'selesai',
        ]);
    }

    #[Test]
    public function siswa_can_view_hasil_after_finish()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'selesai',
                'waktu_mulai' => now()->subHour(),
                'waktu_selesai' => now()->subMinutes(30),
                'nilai' => 85,
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->getJson('/api/siswa/hasil/' . $sesi->id);

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'sesi_id' => $sesi->id,
                'nilai' => 85,
                'status' => 'selesai',
            ],
        ]);
    }

    #[Test]
    public function siswa_cannot_view_hasil_before_finish()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->getJson('/api/siswa/hasil/' . $sesi->id);

        ApiTestHelper::assertApiError($response, 422);
    }

    #[Test]
    public function guru_can_view_all_sesi_for_their_ujian()
    {
        $guruUjian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'guru_id' => $this->guru->id,
            'status' => 'published',
        ]);

        // Create multiple sessions
        $siswaList = ApiTestHelper::createUsers('siswa', 5);
        foreach ($siswaList as $siswa) {
            SesiUjian::create(
                ApiTestHelper::sesiUjianData($guruUjian->id, $siswa->id)
            );
        }

        $headers = ApiTestHelper::authHeaders($this->guru);

        $response = $this->withHeaders($headers)
            ->getJson('/api/guru/ujian/' . $guruUjian->id . '/sesi');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonCount(5, 'data.data');
    }

    #[Test]
    public function guru_cannot_view_sesi_for_others_ujian()
    {
        $otherGuruUjian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'guru_id' => ApiTestHelper::createUser('guru')->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->guru);

        $response = $this->withHeaders($headers)
            ->getJson('/api/guru/ujian/' . $otherGuruUjian->id . '/sesi');

        ApiTestHelper::assertForbidden($response);
    }

    #[Test]
    public function admin_can_force_finish_sesi()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/sesi/' . $sesi->id . '/force-finish');

        ApiTestHelper::assertApiSuccess($response);
        $this->assertDatabaseHas('sesi_ujian', [
            'id' => $sesi->id,
            'status' => 'selesai',
        ]);
    }

    #[Test]
    public function admin_can_reset_sesi()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'selesai',
                'waktu_mulai' => now()->subHour(),
                'waktu_selesai' => now(),
                'nilai' => 75,
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/sesi/' . $sesi->id . '/reset');

        ApiTestHelper::assertApiSuccess($response);
        $this->assertDatabaseHas('sesi_ujian', [
            'id' => $sesi->id,
            'status' => 'belum_mulai',
            'waktu_mulai' => null,
            'waktu_selesai' => null,
            'nilai' => null,
        ]);
    }

    #[Test]
    public function it_calculates_nilai_correctly()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
            ])
        );

        // Submit 7 correct answers out of 10
        foreach ($this->soal->take(7) as $soal) {
            JawabanSiswa::create([
                'sesi_ujian_id' => $sesi->id,
                'soal_id' => $soal->id,
                'jawaban' => $soal->jawaban_benar,
            ]);
        }

        // Submit 3 wrong answers
        foreach ($this->soal->skip(7)->take(3) as $soal) {
            JawabanSiswa::create([
                'sesi_ujian_id' => $sesi->id,
                'soal_id' => $soal->id,
                'jawaban' => 'X', // Wrong answer
            ]);
        }

        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->postJson('/api/siswa/sesi/' . $sesi->id . '/finish');

        ApiTestHelper::assertApiSuccess($response);
        
        // Expected: 7/10 * 100 = 70
        $response->assertJson([
            'data' => [
                'nilai' => 70,
            ],
        ]);
    }

    #[Test]
    public function it_tracks_violation_count()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
                'violation_count' => 0,
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->siswa);

        // Report violation
        $response = $this->withHeaders($headers)
            ->postJson('/api/siswa/sesi/' . $sesi->id . '/violation', [
                'type' => 'tab_switch',
            ]);

        ApiTestHelper::assertApiSuccess($response);
        $this->assertDatabaseHas('sesi_ujian', [
            'id' => $sesi->id,
            'violation_count' => 1,
        ]);
    }

    #[Test]
    public function it_auto_disqualifies_after_max_violations()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
                'violation_count' => 4, // Max is 5
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->siswa);

        // Report 5th violation
        $response = $this->withHeaders($headers)
            ->postJson('/api/siswa/sesi/' . $sesi->id . '/violation', [
                'type' => 'tab_switch',
            ]);

        ApiTestHelper::assertApiSuccess($response);
        $this->assertDatabaseHas('sesi_ujian', [
            'id' => $sesi->id,
            'status' => 'diskualifikasi',
            'violation_count' => 5,
        ]);
    }

    protected function tearDown(): void
    {
        ApiTestHelper::cleanup();
        parent::tearDown();
    }
}
