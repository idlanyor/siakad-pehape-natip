<?php

namespace Tests\Feature\Api;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Feature\Helpers\ApiTestHelper;
use App\Models\PeriodeUjian;
use App\Models\Ujian;
use App\Models\Soal;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UjianTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $guru;
    protected $siswa;
    protected $periode;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = ApiTestHelper::createUser('admin');
        $this->guru = ApiTestHelper::createUser('guru');
        $this->siswa = ApiTestHelper::createUser('siswa');
        
        $this->periode = PeriodeUjian::create(
            ApiTestHelper::periodeUjianData()
        );
    }

    #[Test]
    public function admin_can_create_ujian()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);
        $data = ApiTestHelper::ujianData($this->periode->id);

        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/ujian', $data);

        ApiTestHelper::assertApiSuccess($response, 201);
        $this->assertDatabaseHas('ujian', [
            'judul' => $data['judul'],
            'periode_id' => $this->periode->id,
        ]);
    }

    #[Test]
    public function guru_can_create_ujian()
    {
        $headers = ApiTestHelper::authHeaders($this->guru);
        $data = ApiTestHelper::ujianData($this->periode->id);

        $response = $this->withHeaders($headers)
            ->postJson('/api/guru/ujian', $data);

        ApiTestHelper::assertApiSuccess($response, 201);
        $this->assertDatabaseHas('ujian', [
            'judul' => $data['judul'],
            'guru_id' => $this->guru->id,
        ]);
    }

    #[Test]
    public function siswa_cannot_create_ujian()
    {
        $headers = ApiTestHelper::authHeaders($this->siswa);
        $data = ApiTestHelper::ujianData($this->periode->id);

        $response = $this->withHeaders($headers)
            ->postJson('/api/guru/ujian', $data);

        ApiTestHelper::assertForbidden($response);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_ujian()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/ujian', []);

        ApiTestHelper::assertValidationError($response, [
            'judul',
            'periode_ujian_id',
            'mata_pelajaran',
            'durasi',
            'tanggal_mulai',
        ]);
    }

    #[Test]
    public function it_validates_durasi_is_positive()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);
        $data = ApiTestHelper::ujianData($this->periode->id, [
            'durasi' => -10,
        ]);

        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/ujian', $data);

        ApiTestHelper::assertValidationError($response, ['durasi']);
    }

    #[Test]
    public function it_validates_tanggal_selesai_after_tanggal_mulai()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);
        $data = ApiTestHelper::ujianData($this->periode->id, [
            'tanggal_mulai' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'tanggal_selesai' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/ujian', $data);

        ApiTestHelper::assertValidationError($response, ['tanggal_selesai']);
    }

    #[Test]
    public function admin_can_view_all_ujian()
    {
        Ujian::factory()->count(5)->create([
            'periode_ujian_id' => $this->periode->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/ujian');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonCount(5, 'data.data');
    }

    #[Test]
    public function guru_can_only_view_own_ujian()
    {
        // Create ujian by this guru
        Ujian::factory()->count(3)->create([
            'periode_ujian_id' => $this->periode->id,
            'guru_id' => $this->guru->id,
        ]);

        // Create ujian by other guru
        $otherGuru = ApiTestHelper::createUser('guru');
        Ujian::factory()->count(2)->create([
            'periode_ujian_id' => $this->periode->id,
            'guru_id' => $otherGuru->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->guru);

        $response = $this->withHeaders($headers)
            ->getJson('/api/guru/ujian');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonCount(3, 'data.data');
    }

    #[Test]
    public function it_can_filter_ujian_by_periode()
    {
        $periode2 = PeriodeUjian::create(
            ApiTestHelper::periodeUjianData(['nama' => 'Periode 2'])
        );

        Ujian::factory()->count(3)->create([
            'periode_ujian_id' => $this->periode->id,
        ]);

        Ujian::factory()->count(2)->create([
            'periode_ujian_id' => $periode2->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/ujian?periode_ujian_id=' . $this->periode->id);

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonCount(3, 'data.data');
    }

    #[Test]
    public function it_can_filter_ujian_by_status()
    {
        Ujian::factory()->count(2)->create([
            'periode_ujian_id' => $this->periode->id,
            'status' => 'draft',
        ]);

        Ujian::factory()->count(3)->create([
            'periode_ujian_id' => $this->periode->id,
            'status' => 'published',
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/ujian?status=published');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonCount(3, 'data.data');
    }

    #[Test]
    public function it_can_search_ujian_by_judul()
    {
        Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'judul' => 'Ujian Matematika Kelas 10',
        ]);

        Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'judul' => 'Ujian Bahasa Indonesia',
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/ujian?search=Matematika');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonCount(1, 'data.data');
    }

    #[Test]
    public function admin_can_view_ujian_detail()
    {
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/ujian/' . $ujian->id);

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'id' => $ujian->id,
                'judul' => $ujian->judul,
            ],
        ]);
    }

    #[Test]
    public function guru_can_only_view_own_ujian_detail()
    {
        $ownUjian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'guru_id' => $this->guru->id,
        ]);

        $otherUjian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'guru_id' => ApiTestHelper::createUser('guru')->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->guru);

        // Can view own ujian
        $response = $this->withHeaders($headers)
            ->getJson('/api/guru/ujian/' . $ownUjian->id);
        ApiTestHelper::assertApiSuccess($response);

        // Cannot view other's ujian
        $response = $this->withHeaders($headers)
            ->getJson('/api/guru/ujian/' . $otherUjian->id);
        ApiTestHelper::assertForbidden($response);
    }

    #[Test]
    public function admin_can_update_ujian()
    {
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);
        $updateData = [
            'judul' => 'Updated Judul',
            'durasi_menit' => 120,
        ];

        $response = $this->withHeaders($headers)
            ->putJson('/api/admin/ujian/' . $ujian->id, $updateData);

        ApiTestHelper::assertApiSuccess($response);
        $this->assertDatabaseHas('ujian', [
            'id' => $ujian->id,
            'judul' => 'Updated Judul',
            'durasi_menit' => 120,
        ]);
    }

    #[Test]
    public function guru_can_update_own_ujian()
    {
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'guru_id' => $this->guru->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->guru);
        $updateData = ['judul' => 'Updated by Guru'];

        $response = $this->withHeaders($headers)
            ->putJson('/api/guru/ujian/' . $ujian->id, $updateData);

        ApiTestHelper::assertApiSuccess($response);
        $this->assertDatabaseHas('ujian', [
            'id' => $ujian->id,
            'judul' => 'Updated by Guru',
        ]);
    }

    #[Test]
    public function guru_cannot_update_others_ujian()
    {
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'guru_id' => ApiTestHelper::createUser('guru')->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->guru);

        $response = $this->withHeaders($headers)
            ->putJson('/api/guru/ujian/' . $ujian->id, [
                'judul' => 'Hacked',
            ]);

        ApiTestHelper::assertForbidden($response);
    }

    #[Test]
    public function cannot_update_published_ujian_that_has_started()
    {
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'status' => 'published',
            'tanggal_mulai' => now()->subHour(),
            'tanggal_selesai' => now()->addHour(),
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->putJson('/api/admin/ujian/' . $ujian->id, [
                'judul' => 'Cannot Update',
            ]);

        ApiTestHelper::assertApiError($response, 422);
    }

    #[Test]
    public function admin_can_delete_ujian()
    {
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->deleteJson('/api/admin/ujian/' . $ujian->id);

        ApiTestHelper::assertApiSuccess($response);
        $this->assertSoftDeleted('ujian', ['id' => $ujian->id]);
    }

    #[Test]
    public function guru_can_delete_own_ujian()
    {
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'guru_id' => $this->guru->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->guru);

        $response = $this->withHeaders($headers)
            ->deleteJson('/api/guru/ujian/' . $ujian->id);

        ApiTestHelper::assertApiSuccess($response);
        $this->assertSoftDeleted('ujian', ['id' => $ujian->id]);
    }

    #[Test]
    public function cannot_delete_ujian_with_active_sessions()
    {
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
        ]);

        // Create active session
        $ujian->sesiUjians()->create(
            ApiTestHelper::sesiUjianData($ujian->id, $this->siswa->id, [
                'status' => 'sedang_mengerjakan',
            ])
        );

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->deleteJson('/api/admin/ujian/' . $ujian->id);

        ApiTestHelper::assertApiError($response, 422);
    }

    #[Test]
    public function it_can_publish_ujian()
    {
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'status' => 'draft',
        ]);

        // Add soal
        Soal::factory()->count(10)->create(['ujian_id' => $ujian->id]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/ujian/' . $ujian->id . '/publish');

        ApiTestHelper::assertApiSuccess($response);
        $this->assertDatabaseHas('ujian', [
            'id' => $ujian->id,
            'status' => 'published',
        ]);
    }

    #[Test]
    public function cannot_publish_ujian_without_soal()
    {
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'status' => 'draft',
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/ujian/' . $ujian->id . '/publish');

        ApiTestHelper::assertApiError($response, 422);
    }

    #[Test]
    public function it_can_unpublish_ujian()
    {
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $this->periode->id,
            'status' => 'published',
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/ujian/' . $ujian->id . '/unpublish');

        ApiTestHelper::assertApiSuccess($response);
        $this->assertDatabaseHas('ujian', [
            'id' => $ujian->id,
            'status' => 'draft',
        ]);
    }

    #[Test]
    public function it_returns_404_for_non_existent_ujian()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/ujian/99999');

        ApiTestHelper::assertNotFound($response);
    }

    protected function tearDown(): void
    {
        ApiTestHelper::cleanup();
        parent::tearDown();
    }
}
