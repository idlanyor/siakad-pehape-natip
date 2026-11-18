<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Models\Ujian;
use App\Models\PeriodeUjian;
use App\Models\SoalUjian;
use App\Models\SesiUjian;
use App\Models\JawabanSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class SesiUjianFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $ujian;
    protected $siswaData;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test periode
        $periode = PeriodeUjian::create([
            'nama' => 'UTS Semester 1',
            'tahun_ajaran' => '2024/2025',
            'semester' => 1,
            'tanggal_mulai' => now()->subDays(5),
            'tanggal_selesai' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Create test ujian
        $this->ujian = Ujian::create([
            'periode_id' => $periode->id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Matematika',
            'deskripsi' => 'Ujian bab 1-5',
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 3,
            'is_published' => true,
            'acak_soal' => false,
            'acak_opsi' => false,
            'tampilkan_nilai' => true,
        ]);

        // Create test soals using factory for consistency
        SoalUjian::factory()->create([
            'ujian_id' => $this->ujian->id,
            'nomor' => 1,
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Berapakah 2 + 2?',
            'opsi_a' => '3',
            'opsi_b' => '4',
            'opsi_c' => '5',
            'opsi_d' => '6',
            'kunci_jawaban' => 'B',
            'bobot_nilai' => 1,
        ]);

        SoalUjian::factory()->create([
            'ujian_id' => $this->ujian->id,
            'nomor' => 2,
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Berapakah 5 x 3?',
            'opsi_a' => '10',
            'opsi_b' => '15',
            'opsi_c' => '20',
            'opsi_d' => '25',
            'kunci_jawaban' => 'B',
            'bobot_nilai' => 1,
        ]);

        SoalUjian::factory()->create([
            'ujian_id' => $this->ujian->id,
            'nomor' => 3,
            'tipe' => 'essay',
            'pertanyaan' => 'Jelaskan teorema Pythagoras',
            'kunci_jawaban' => null,
            'bobot_nilai' => 2,
        ]);

        $this->siswaData = [
            'user_id' => 10,
            'nama' => 'Ahmad Siswa',
            'role' => 'siswa',
            'kelas_id' => 10,
            'nis' => '2024001',
        ];
    }

    #[Test]
    public function siswa_can_view_available_ujian()
    {
        $response = $this->authenticatedRequest('GET', '/api/sesi', [], $this->siswaData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'judul',
                        'waktu_mulai',
                        'waktu_selesai',
                        'durasi_menit',
                        'dapat_dikerjakan',
                    ],
                ],
            ]);
    }

    #[Test]
    public function siswa_can_start_exam_session()
    {
        $response = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Sesi ujian berhasil dimulai',
            ])
            ->assertJsonStructure([
                'data' => [
                    'sesi_id',
                    'waktu_mulai',
                    'durasi_detik',
                    'waktu_berakhir',
                ],
            ]);

        $this->assertDatabaseHas('sesi_ujian', [
            'ujian_id' => $this->ujian->id,
            'siswa_id' => 10,
            'status' => 'berlangsung',
            'is_resmi' => true,
        ]);

        // Check jawaban placeholders created
        $sesi = SesiUjian::where('ujian_id', $this->ujian->id)
            ->where('siswa_id', 10)
            ->first();

        $this->assertEquals(3, $sesi->jawabans()->count());
    }

    #[Test]
    public function siswa_cannot_start_session_for_unpublished_ujian()
    {
        $this->ujian->update(['is_published' => false]);

        $response = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Ujian belum dipublish',
            ]);
    }

    #[Test]
    public function siswa_cannot_start_session_outside_time_window()
    {
        $this->ujian->update([
            'waktu_mulai' => now()->addDays(1),
            'waktu_selesai' => now()->addDays(2),
        ]);

        $response = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Ujian tidak dalam waktu pelaksanaan',
            ]);
    }

    #[Test]
    public function siswa_cannot_start_session_for_different_kelas()
    {
        $response = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], [
            'user_id' => 11,
            'nama' => 'Budi',
            'role' => 'siswa',
            'kelas_id' => 11, // Different kelas
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Ujian tidak tersedia untuk kelas Anda',
            ]);
    }

    #[Test]
    public function siswa_cannot_start_multiple_sessions()
    {
        // Start first session
        $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);

        // Try to start second session
        $response = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Anda sudah memiliki sesi aktif untuk ujian ini',
            ]);
    }

    #[Test]
    public function siswa_can_get_session_with_soals()
    {
        // Start session
        $startResponse = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);
        $sesiId = $startResponse->json('data.sesi_id');

        // Get session
        $response = $this->authenticatedRequest('GET', "/api/sesi/{$sesiId}", [], $this->siswaData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'sesi' => [
                        'id',
                        'ujian_judul',
                        'status',
                        'sisa_waktu_detik',
                    ],
                    'soals' => [
                        '*' => [
                            'id',
                            'nomor',
                            'tipe',
                            'pertanyaan',
                            'jawaban_id',
                        ],
                    ],
                    'progress' => [
                        'terjawab',
                        'ragu',
                        'total',
                    ],
                ],
            ]);

        // Kunci jawaban should not be visible during exam
        $soals = $response->json('data.soals');
        foreach ($soals as $soal) {
            $this->assertNull($soal['kunci_jawaban']);
        }
    }

    #[Test]
    public function siswa_can_submit_answer()
    {
        // Start session
        $startResponse = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);
        $sesiId = $startResponse->json('data.sesi_id');

        // Get session to get jawaban_id
        $sessionResponse = $this->authenticatedRequest('GET', "/api/sesi/{$sesiId}", [], $this->siswaData);
        $jawabanId = $sessionResponse->json('data.soals.0.jawaban_id');

        // Submit answer
        $response = $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/answer", [
            'jawaban_id' => $jawabanId,
            'jawaban' => 'B',
            'is_ragu' => false,
        ], $this->siswaData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Jawaban berhasil disimpan',
            ]);

        $this->assertDatabaseHas('jawaban_siswa', [
            'id' => $jawabanId,
            'jawaban' => 'B',
            'is_ragu' => false,
        ]);
    }

    #[Test]
    public function siswa_can_mark_answer_as_ragu()
    {
        // Start session
        $startResponse = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);
        $sesiId = $startResponse->json('data.sesi_id');

        // Get jawaban_id
        $sessionResponse = $this->authenticatedRequest('GET', "/api/sesi/{$sesiId}", [], $this->siswaData);
        $jawabanId = $sessionResponse->json('data.soals.0.jawaban_id');

        // Submit answer with ragu flag
        $response = $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/answer", [
            'jawaban_id' => $jawabanId,
            'jawaban' => 'A',
            'is_ragu' => true,
        ], $this->siswaData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('jawaban_siswa', [
            'id' => $jawabanId,
            'is_ragu' => true,
        ]);
    }

    #[Test]
    public function siswa_can_submit_entire_exam()
    {
        // Start session
        $startResponse = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);
        $sesiId = $startResponse->json('data.sesi_id');

        // Get session and submit answers
        $sessionResponse = $this->authenticatedRequest('GET', "/api/sesi/{$sesiId}", [], $this->siswaData);
        $soals = $sessionResponse->json('data.soals');

        // Answer first two questions correctly
        $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/answer", [
            'jawaban_id' => $soals[0]['jawaban_id'],
            'jawaban' => 'B', // Correct
        ], $this->siswaData);

        $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/answer", [
            'jawaban_id' => $soals[1]['jawaban_id'],
            'jawaban' => 'B', // Correct
        ], $this->siswaData);

        // Answer essay (will be graded manually)
        $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/answer", [
            'jawaban_id' => $soals[2]['jawaban_id'],
            'jawaban' => 'Teorema Pythagoras adalah...',
        ], $this->siswaData);

        // Submit exam
        $response = $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/submit", [], $this->siswaData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ujian berhasil diselesaikan',
            ])
            ->assertJsonStructure([
                'data' => [
                    'nilai_akhir',
                    'tampilkan_nilai',
                ],
            ]);

        // Check sesi status
        $this->assertDatabaseHas('sesi_ujian', [
            'id' => $sesiId,
            'status' => 'selesai',
        ]);

        // Check auto-grading (2 correct out of 2 pilihan ganda = 100%, essay not graded yet)
        $sesi = SesiUjian::find($sesiId);
        $this->assertNotNull($sesi->nilai_akhir);
        $this->assertNotNull($sesi->waktu_selesai);
    }

    #[Test]
    public function exam_auto_submits_on_timeout()
    {
        // Create ujian with very short duration
        $shortUjian = Ujian::create([
            'periode_id' => $this->ujian->periode_id,
            'guru_id' => 5,
            'guru_nama' => 'Pak Budi',
            'kelas_id' => 10,
            'mapel_id' => 5,
            'judul' => 'Ujian Cepat',
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->addHours(2),
            'durasi_menit' => 1, // 1 minute
            'passing_grade' => 75,
            'jumlah_soal' => 1,
            'is_published' => true,
        ]);

        SoalUjian::factory()->create([
            'ujian_id' => $shortUjian->id,
            'nomor' => 1,
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Test',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'kunci_jawaban' => 'A',
            'bobot_nilai' => 1,
        ]);

        // Start session
        $startResponse = $this->authenticatedRequest('POST', "/api/ujian/{$shortUjian->id}/sesi", [], $this->siswaData);
        $sesiId = $startResponse->json('data.sesi_id');

        // Manually set waktu_mulai to past to simulate timeout
        $sesi = SesiUjian::find($sesiId);
        $sesi->update(['waktu_mulai' => now()->subMinutes(2)]);

        // Try to get session (should auto-submit)
        $response = $this->authenticatedRequest('GET', "/api/sesi/{$sesiId}", [], $this->siswaData);

        $response->assertStatus(200);

        // Check sesi was auto-submitted
        $sesi->refresh();
        $this->assertEquals('timeout', $sesi->status);
        $this->assertNotNull($sesi->nilai_akhir);
    }

    #[Test]
    public function siswa_can_view_exam_result()
    {
        // Start and complete exam
        $startResponse = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);
        $sesiId = $startResponse->json('data.sesi_id');

        // Submit exam
        $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/submit", [], $this->siswaData);

        // Get result
        $response = $this->authenticatedRequest('GET', "/api/sesi/{$sesiId}/result", [], $this->siswaData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'sesi' => [
                        'id',
                        'ujian_judul',
                        'siswa_nama',
                        'status',
                        'nilai_akhir',
                        'passing_grade',
                        'lulus',
                    ],
                    'jawabans' => [
                        '*' => [
                            'nomor',
                            'pertanyaan',
                            'jawaban',
                            'kunci_jawaban',
                            'is_benar',
                        ],
                    ],
                    'statistik' => [
                        'benar',
                        'salah',
                        'belum_dinilai',
                    ],
                ],
            ]);

        // Kunci jawaban should be visible after exam for pilihan_ganda only
        $jawabans = $response->json('data.jawabans');
        foreach ($jawabans as $jawaban) {
            if ($jawaban['tipe'] === 'pilihan_ganda') {
                $this->assertNotNull($jawaban['kunci_jawaban']);
            }
        }
    }

    #[Test]
    public function siswa_cannot_view_result_of_ongoing_exam()
    {
        // Start exam
        $startResponse = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);
        $sesiId = $startResponse->json('data.sesi_id');

        // Try to get result without submitting
        $response = $this->authenticatedRequest('GET', "/api/sesi/{$sesiId}/result", [], $this->siswaData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Sesi ujian belum selesai',
            ]);
    }

    #[Test]
    public function siswa_cannot_access_other_student_session()
    {
        // Start session as first student
        $startResponse = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);
        $sesiId = $startResponse->json('data.sesi_id');

        // Try to access as different student
        $response = $this->authenticatedRequest('GET', "/api/sesi/{$sesiId}", [], [
            'user_id' => 11,
            'nama' => 'Budi',
            'role' => 'siswa',
            'kelas_id' => 10,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function guru_can_view_student_result()
    {
        // Start and complete exam as student
        $startResponse = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);
        $sesiId = $startResponse->json('data.sesi_id');
        $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/submit", [], $this->siswaData);

        // View result as guru
        $response = $this->authenticatedRequest('GET', "/api/sesi/{$sesiId}/result", [], [
            'user_id' => 5,
            'nama' => 'Pak Budi',
            'role' => 'guru',
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function auto_grading_works_correctly()
    {
        // Start session
        $startResponse = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);
        $sesiId = $startResponse->json('data.sesi_id');

        // Get session
        $sessionResponse = $this->authenticatedRequest('GET', "/api/sesi/{$sesiId}", [], $this->siswaData);
        $soals = $sessionResponse->json('data.soals');

        // Answer first question correctly
        $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/answer", [
            'jawaban_id' => $soals[0]['jawaban_id'],
            'jawaban' => 'B', // Correct
        ], $this->siswaData);

        // Answer second question incorrectly
        $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/answer", [
            'jawaban_id' => $soals[1]['jawaban_id'],
            'jawaban' => 'A', // Wrong
        ], $this->siswaData);

        // Submit exam
        $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/submit", [], $this->siswaData);

        // Check grading
        $jawaban1 = JawabanSiswa::find($soals[0]['jawaban_id']);
        $jawaban2 = JawabanSiswa::find($soals[1]['jawaban_id']);

        $this->assertTrue($jawaban1->is_benar);
        $this->assertEquals(1, $jawaban1->nilai);

        $this->assertFalse($jawaban2->is_benar);
        $this->assertEquals(0, $jawaban2->nilai);
    }

    #[Test]
    public function essay_questions_are_not_auto_graded()
    {
        // Start session
        $startResponse = $this->authenticatedRequest('POST', "/api/ujian/{$this->ujian->id}/sesi", [], $this->siswaData);
        $sesiId = $startResponse->json('data.sesi_id');

        // Get session
        $sessionResponse = $this->authenticatedRequest('GET', "/api/sesi/{$sesiId}", [], $this->siswaData);
        $soals = $sessionResponse->json('data.soals');

        // Find essay question
        $essaySoal = collect($soals)->firstWhere('tipe', 'essay');

        // Answer essay
        $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/answer", [
            'jawaban_id' => $essaySoal['jawaban_id'],
            'jawaban' => 'My essay answer',
        ], $this->siswaData);

        // Submit exam
        $this->authenticatedRequest('POST', "/api/sesi/{$sesiId}/submit", [], $this->siswaData);

        // Check essay is not graded
        $jawaban = JawabanSiswa::find($essaySoal['jawaban_id']);
        $this->assertNull($jawaban->is_benar);
        $this->assertNull($jawaban->nilai);
    }
}