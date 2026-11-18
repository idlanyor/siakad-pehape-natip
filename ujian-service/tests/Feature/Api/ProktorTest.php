<?php

namespace Tests\Feature\Api;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Feature\Helpers\ApiTestHelper;
use App\Models\PeriodeUjian;
use App\Models\Ujian;
use App\Models\Soal;
use App\Models\SesiUjian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\SesiUpdated;
use App\Events\AnnouncementEvent;

class ProktorTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $guru;
    protected $siswa;
    protected $periode;
    protected $ujian;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = ApiTestHelper::createUser('admin');
        $this->guru = ApiTestHelper::createUser('guru');
        $this->siswa = ApiTestHelper::createUser('siswa');
        
        $this->periode = PeriodeUjian::create(
            ApiTestHelper::periodeUjianData()
        );

        $this->ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'guru_id' => $this->guru->id,
            'status' => 'published',
            'tanggal_mulai' => now()->subHour(),
            'tanggal_selesai' => now()->addHour(),
        ]);

        Soal::factory()->count(10)->create([
            'ujian_id' => $this->ujian->id,
        ]);
    }

    #[Test]
    public function admin_can_access_proktor_dashboard()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/dashboard');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'statistics' => [
                    'total_ujian_aktif',
                    'total_siswa_aktif',
                    'total_selesai',
                    'total_violations',
                ],
                'recent_events',
            ],
        ]);
    }

    #[Test]
    public function guru_can_access_proktor_for_own_ujian()
    {
        $headers = ApiTestHelper::authHeaders($this->guru);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/ujian/' . $this->ujian->id);

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'ujian',
                'sessions',
                'statistics',
            ],
        ]);
    }

    #[Test]
    public function guru_cannot_access_proktor_for_others_ujian()
    {
        $otherGuru = ApiTestHelper::createUser('guru');
        $otherUjian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'guru_id' => $otherGuru->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->guru);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/ujian/' . $otherUjian->id);

        ApiTestHelper::assertForbidden($response);
    }

    #[Test]
    public function siswa_cannot_access_proktor_dashboard()
    {
        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/dashboard');

        ApiTestHelper::assertForbidden($response);
    }

    #[Test]
    public function it_returns_active_sessions_for_ujian()
    {
        // Create multiple sessions
        $siswaList = ApiTestHelper::createUsers('siswa', 5);
        
        foreach ($siswaList->take(3) as $siswa) {
            SesiUjian::create(
                ApiTestHelper::sesiUjianData($this->ujian->id, $siswa->id, [
                    'status' => 'sedang_mengerjakan',
                    'waktu_mulai' => now()->subMinutes(30),
                ])
            );
        }

        foreach ($siswaList->skip(3)->take(2) as $siswa) {
            SesiUjian::create(
                ApiTestHelper::sesiUjianData($this->ujian->id, $siswa->id, [
                    'status' => 'selesai',
                    'waktu_mulai' => now()->subHour(),
                    'waktu_selesai' => now()->subMinutes(30),
                ])
            );
        }

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/ujian/' . $this->ujian->id . '/sessions?status=sedang_mengerjakan');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_returns_session_with_realtime_data()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now()->subMinutes(30),
                'violation_count' => 2,
                'last_activity' => now()->subMinutes(1),
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/sesi/' . $sesi->id);

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'siswa',
                'status',
                'waktu_mulai',
                'sisa_waktu',
                'violation_count',
                'last_activity',
                'connection_status',
                'progress',
            ],
        ]);
    }

    #[Test]
    public function admin_can_force_finish_session()
    {
        Event::fake([SesiUpdated::class]);

        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now()->subMinutes(30),
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/proktor/sesi/' . $sesi->id . '/force-finish', [
                'reason' => 'Pelanggaran berat',
            ]);

        ApiTestHelper::assertApiSuccess($response);
        
        $this->assertDatabaseHas('sesi_ujian', [
            'id' => $sesi->id,
            'status' => 'selesai',
        ]);

        Event::assertDispatched(SesiUpdated::class);
    }

    #[Test]
    public function guru_can_force_finish_session_for_own_ujian()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->guru);

        $response = $this->withHeaders($headers)
            ->postJson('/api/proktor/sesi/' . $sesi->id . '/force-finish', [
                'reason' => 'Technical issue',
            ]);

        ApiTestHelper::assertApiSuccess($response);
    }

    #[Test]
    public function guru_cannot_force_finish_session_for_others_ujian()
    {
        $otherGuru = ApiTestHelper::createUser('guru');
        $otherUjian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'guru_id' => $otherGuru->id,
        ]);

        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($otherUjian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->guru);

        $response = $this->withHeaders($headers)
            ->postJson('/api/proktor/sesi/' . $sesi->id . '/force-finish');

        ApiTestHelper::assertForbidden($response);
    }

    #[Test]
    public function admin_can_disqualify_session()
    {
        Event::fake([SesiUpdated::class]);

        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
                'violation_count' => 3,
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/proktor/sesi/' . $sesi->id . '/disqualify', [
                'reason' => 'Kecurangan terdeteksi',
            ]);

        ApiTestHelper::assertApiSuccess($response);
        
        $this->assertDatabaseHas('sesi_ujian', [
            'id' => $sesi->id,
            'status' => 'diskualifikasi',
        ]);

        Event::assertDispatched(SesiUpdated::class);
    }

    #[Test]
    public function it_validates_disqualify_reason()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/proktor/sesi/' . $sesi->id . '/disqualify', []);

        ApiTestHelper::assertValidationError($response, ['reason']);
    }

    #[Test]
    public function admin_can_send_announcement_to_all()
    {
        Event::fake([AnnouncementEvent::class]);

        // Create multiple active sessions
        $siswaList = ApiTestHelper::createUsers('siswa', 3);
        foreach ($siswaList as $siswa) {
            SesiUjian::create(
                ApiTestHelper::sesiUjianData($this->ujian->id, $siswa->id, [
                    'status' => 'sedang_mengerjakan',
                ])
            );
        }

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/proktor/ujian/' . $this->ujian->id . '/announce', [
                'message' => 'Sisa waktu 10 menit',
                'type' => 'warning',
            ]);

        ApiTestHelper::assertApiSuccess($response);
        Event::assertDispatched(AnnouncementEvent::class);
    }

    #[Test]
    public function admin_can_send_announcement_to_specific_session()
    {
        Event::fake([AnnouncementEvent::class]);

        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/proktor/sesi/' . $sesi->id . '/announce', [
                'message' => 'Periksa jawaban Anda',
                'type' => 'info',
            ]);

        ApiTestHelper::assertApiSuccess($response);
        Event::assertDispatched(AnnouncementEvent::class);
    }

    #[Test]
    public function it_validates_announcement_message()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/proktor/ujian/' . $this->ujian->id . '/announce', [
                'type' => 'info',
            ]);

        ApiTestHelper::assertValidationError($response, ['message']);
    }

    #[Test]
    public function it_returns_recent_events()
    {
        // Create some sessions with activities
        $siswaList = ApiTestHelper::createUsers('siswa', 3);
        
        foreach ($siswaList as $siswa) {
            $sesi = SesiUjian::create(
                ApiTestHelper::sesiUjianData($this->ujian->id, $siswa->id, [
                    'status' => 'sedang_mengerjakan',
                    'waktu_mulai' => now()->subMinutes(rand(10, 60)),
                ])
            );

            // Log some events
            \DB::table('proktor_events')->insert([
                'sesi_ujian_id' => $sesi->id,
                'event_type' => 'started',
                'description' => 'Siswa memulai ujian',
                'created_at' => now()->subMinutes(rand(5, 30)),
            ]);
        }

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/ujian/' . $this->ujian->id . '/events?limit=10');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'event_type',
                    'description',
                    'created_at',
                    'sesi',
                ],
            ],
        ]);
    }

    #[Test]
    public function it_filters_events_by_type()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
            ])
        );

        // Create different event types
        \DB::table('proktor_events')->insert([
            ['sesi_ujian_id' => $sesi->id, 'event_type' => 'started', 'description' => 'Started', 'created_at' => now()],
            ['sesi_ujian_id' => $sesi->id, 'event_type' => 'violation', 'description' => 'Violation', 'created_at' => now()],
            ['sesi_ujian_id' => $sesi->id, 'event_type' => 'violation', 'description' => 'Another violation', 'created_at' => now()],
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/ujian/' . $this->ujian->id . '/events?type=violation');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_returns_statistics_for_ujian()
    {
        $siswaList = ApiTestHelper::createUsers('siswa', 10);
        
        // 5 active, 3 finished, 2 disqualified
        foreach ($siswaList->take(5) as $siswa) {
            SesiUjian::create(
                ApiTestHelper::sesiUjianData($this->ujian->id, $siswa->id, [
                    'status' => 'sedang_mengerjakan',
                ])
            );
        }

        foreach ($siswaList->skip(5)->take(3) as $siswa) {
            SesiUjian::create(
                ApiTestHelper::sesiUjianData($this->ujian->id, $siswa->id, [
                    'status' => 'selesai',
                ])
            );
        }

        foreach ($siswaList->skip(8)->take(2) as $siswa) {
            SesiUjian::create(
                ApiTestHelper::sesiUjianData($this->ujian->id, $siswa->id, [
                    'status' => 'diskualifikasi',
                ])
            );
        }

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/ujian/' . $this->ujian->id . '/statistics');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'total_peserta' => 10,
                'sedang_mengerjakan' => 5,
                'selesai' => 3,
                'diskualifikasi' => 2,
            ],
        ]);
    }

    #[Test]
    public function it_tracks_connection_status()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
                'last_activity' => now()->subMinutes(1),
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/sesi/' . $sesi->id);

        ApiTestHelper::assertApiSuccess($response);
        
        // Connection status should be 'online' if last_activity < 2 minutes
        $response->assertJson([
            'data' => [
                'connection_status' => 'online',
            ],
        ]);
    }

    #[Test]
    public function it_detects_idle_connection()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
                'last_activity' => now()->subMinutes(3),
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/sesi/' . $sesi->id);

        ApiTestHelper::assertApiSuccess($response);
        
        // Connection status should be 'idle' if last_activity between 2-5 minutes
        $response->assertJson([
            'data' => [
                'connection_status' => 'idle',
            ],
        ]);
    }

    #[Test]
    public function it_detects_offline_connection()
    {
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($this->ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
                'waktu_mulai' => now(),
                'last_activity' => now()->subMinutes(10),
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/sesi/' . $sesi->id);

        ApiTestHelper::assertApiSuccess($response);
        
        // Connection status should be 'offline' if last_activity > 5 minutes
        $response->assertJson([
            'data' => [
                'connection_status' => 'offline',
            ],
        ]);
    }

    #[Test]
    public function it_returns_list_of_active_ujian()
    {
        // Create multiple ujian
        Ujian::factory()->count(3)->create([
            'periode_ujian_id' => $this->periode->id,
            'status' => 'published',
            'tanggal_mulai' => now()->subHour(),
            'tanggal_selesai' => now()->addHour(),
        ]);

        Ujian::factory()->count(2)->create([
            'periode_ujian_id' => $this->periode->id,
            'status' => 'draft',
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/proktor/ujian?status=active');

        ApiTestHelper::assertApiSuccess($response);
        
        // Should return 4 active ujian (3 new + 1 from setUp)
        $response->assertJsonCount(4, 'data');
    }

    protected function tearDown(): void
    {
        ApiTestHelper::cleanup();
        parent::tearDown();
    }
}
