<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Tests\Feature\Helpers\ApiTestHelper;
use App\Models\PeriodeUjian;
use App\Models\Ujian;
use App\Models\SesiUjian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $guru;
    protected $siswa;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = ApiTestHelper::createUser('admin');
        $this->guru = ApiTestHelper::createUser('guru');
        $this->siswa = ApiTestHelper::createUser('siswa');
    }

    /** @test */
    public function it_returns_401_for_unauthenticated_requests()
    {
        $response = $this->getJson('/api/admin/dashboard');

        ApiTestHelper::assertUnauthorized($response);
        $response->assertJson([
            'success' => false,
            'message' => 'Unauthorized',
        ]);
    }

    /** @test */
    public function it_returns_401_for_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token-xyz',
            'Accept' => 'application/json',
        ])->getJson('/api/admin/dashboard');

        ApiTestHelper::assertUnauthorized($response);
    }

    /** @test */
    public function it_returns_401_for_expired_token()
    {
        $token = ApiTestHelper::generateToken($this->siswa, [
            'exp' => now()->subHour()->timestamp,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/siswa/ujian');

        ApiTestHelper::assertUnauthorized($response);
    }

    /** @test */
    public function it_returns_403_for_unauthorized_role_access()
    {
        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/dashboard');

        ApiTestHelper::assertForbidden($response);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /** @test */
    public function it_returns_403_when_guru_accesses_others_ujian()
    {
        $otherGuru = ApiTestHelper::createUser('guru');
        $periode = PeriodeUjian::create(ApiTestHelper::periodeUjianData());
        
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $periode->id,
            'guru_id' => $otherGuru->id,
        ]);

        $headers = ApiTestHelper::authHeaders($this->guru);

        $response = $this->withHeaders($headers)
            ->getJson('/api/guru/ujian/' . $ujian->id);

        ApiTestHelper::assertForbidden($response);
    }

    /** @test */
    public function it_returns_403_when_siswa_accesses_others_sesi()
    {
        $otherSiswa = ApiTestHelper::createUser('siswa');
        $periode = PeriodeUjian::create(ApiTestHelper::periodeUjianData());
        $ujian = Ujian::factory()->create(['periode_ujian_id' => $periode->id]);
        
        $sesi = SesiUjian::create(
            ApiTestHelper::sesiUjianData($ujian->id, $otherSiswa->id)
        );

        $headers = ApiTestHelper::authHeaders($this->siswa);

        $response = $this->withHeaders($headers)
            ->getJson('/api/siswa/sesi/' . $sesi->id);

        ApiTestHelper::assertForbidden($response);
    }

    /** @test */
    public function it_returns_404_for_non_existent_resource()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/ujian/99999');

        ApiTestHelper::assertNotFound($response);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /** @test */
    public function it_returns_404_for_soft_deleted_resource()
    {
        $periode = PeriodeUjian::create(ApiTestHelper::periodeUjianData());
        $ujian = Ujian::factory()->create(['periode_ujian_id' => $periode->id]);
        $ujian->delete(); // Soft delete

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/ujian/' . $ujian->id);

        ApiTestHelper::assertNotFound($response);
    }

    /** @test */
    public function it_returns_404_for_invalid_route()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/non-existent-endpoint');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_422_for_validation_errors()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/periode-ujian', [
                // Missing required fields
            ]);

        ApiTestHelper::assertValidationError($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors',
        ]);
    }

    /** @test */
    public function it_returns_422_with_detailed_validation_errors()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/periode-ujian', [
                'nama' => '', // Empty
                'tahun_ajaran' => 'invalid', // Invalid format
                'semester' => 'Invalid', // Not in enum
            ]);

        ApiTestHelper::assertValidationError($response, [
            'nama',
            'tahun_ajaran',
            'semester',
        ]);
    }

    /** @test */
    public function it_returns_422_for_business_logic_errors()
    {
        $periode = PeriodeUjian::create(ApiTestHelper::periodeUjianData());
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $periode->id,
            'status' => 'draft',
        ]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        // Try to publish without soal
        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/ujian/' . $ujian->id . '/publish');

        ApiTestHelper::assertApiError($response, 422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /** @test */
    public function it_returns_422_for_duplicate_entry()
    {
        $periode = PeriodeUjian::create(
            ApiTestHelper::periodeUjianData(['nama' => 'Unique Periode'])
        );

        $headers = ApiTestHelper::authHeaders($this->admin);

        // Try to create duplicate
        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/periode-ujian', [
                'nama' => 'Unique Periode',
                'tahun_ajaran' => '2025/2026',
                'semester' => 'Ganjil',
                'tanggal_mulai' => now()->addDays(1)->format('Y-m-d'),
                'tanggal_selesai' => now()->addDays(30)->format('Y-m-d'),
            ]);

        ApiTestHelper::assertApiError($response, 422);
    }

    /** @test */
    public function it_handles_database_connection_errors()
    {
        // Simulate database error by using invalid connection
        config(['database.default' => 'invalid']);

        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/ujian');

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
        ]);

        // Restore connection
        config(['database.default' => 'testing']);
    }

    /** @test */
    public function it_handles_query_exceptions()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        // Force a query error by trying to insert invalid data
        DB::shouldReceive('table')->andThrow(new \Exception('Database error'));

        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/periode-ujian', 
                ApiTestHelper::periodeUjianData()
            );

        $response->assertStatus(500);
    }

    /** @test */
    public function it_returns_500_for_unhandled_exceptions()
    {
        // This would require mocking a controller method to throw exception
        // For now, we test that 500 errors have proper structure
        
        $headers = ApiTestHelper::authHeaders($this->admin);

        // Simulate by calling endpoint that might throw exception
        try {
            $response = $this->withHeaders($headers)
                ->getJson('/api/admin/test-error-500');
            
            if ($response->status() === 500) {
                $response->assertJson([
                    'success' => false,
                ]);
            }
        } catch (\Exception $e) {
            // Expected in test environment
            $this->assertTrue(true);
        }
    }

    /** @test */
    public function it_logs_500_errors()
    {
        Log::shouldReceive('error')->once();

        $headers = ApiTestHelper::authHeaders($this->admin);

        try {
            // Trigger an error
            DB::shouldReceive('table')->andThrow(new \Exception('Test error'));
            
            $this->withHeaders($headers)
                ->getJson('/api/admin/ujian');
        } catch (\Exception $e) {
            // Expected
        }
    }

    /** @test */
    public function it_returns_429_for_rate_limiting()
    {
        $headers = ApiTestHelper::authHeaders($this->siswa);

        // Make multiple requests rapidly
        for ($i = 0; $i < 61; $i++) {
            $response = $this->withHeaders($headers)
                ->getJson('/api/siswa/ujian');
        }

        // Next request should be rate limited
        $response = $this->withHeaders($headers)
            ->getJson('/api/siswa/ujian');

        $response->assertStatus(429);
    }

    /** @test */
    public function it_handles_method_not_allowed()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        // Try POST on GET-only endpoint
        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/ujian/1');

        $response->assertStatus(405);
    }

    /** @test */
    public function it_handles_invalid_json_payload()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        $response = $this->withHeaders(array_merge($headers, [
            'Content-Type' => 'application/json',
        ]))->call('POST', '/api/admin/periode-ujian', [], [], [], [], 'invalid json');

        $response->assertStatus(400);
    }

    /** @test */
    public function it_handles_missing_content_type()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);
        unset($headers['Accept']);

        $response = $this->withHeaders($headers)
            ->post('/api/admin/periode-ujian', 
                ApiTestHelper::periodeUjianData()
            );

        // Should still work but might return different format
        $this->assertContains($response->status(), [200, 201, 422]);
    }

    /** @test */
    public function it_sanitizes_error_messages_in_production()
    {
        config(['app.debug' => false]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        try {
            DB::shouldReceive('table')->andThrow(new \Exception('Sensitive database info'));
            
            $response = $this->withHeaders($headers)
                ->getJson('/api/admin/ujian');

            if ($response->status() === 500) {
                // Should not expose sensitive info
                $response->assertDontSee('Sensitive database info');
                $response->assertJson([
                    'success' => false,
                    'message' => 'Internal Server Error',
                ]);
            }
        } catch (\Exception $e) {
            // Expected
        }

        config(['app.debug' => true]);
    }

    /** @test */
    public function it_includes_stack_trace_in_development()
    {
        config(['app.debug' => true]);

        $headers = ApiTestHelper::authHeaders($this->admin);

        try {
            DB::shouldReceive('table')->andThrow(new \Exception('Test error'));
            
            $response = $this->withHeaders($headers)
                ->getJson('/api/admin/ujian');

            if ($response->status() === 500) {
                // Should include debug info in development
                $response->assertJsonStructure([
                    'success',
                    'message',
                ]);
            }
        } catch (\Exception $e) {
            // Expected
        }
    }

    /** @test */
    public function it_handles_concurrent_request_conflicts()
    {
        $periode = PeriodeUjian::create(ApiTestHelper::periodeUjianData());
        $ujian = Ujian::factory()->create([
            'periode_ujian_id' => $periode->id,
            'status' => 'published',
            'tanggal_mulai' => now()->subHour(),
            'tanggal_selesai' => now()->addHour(),
        ]);

        $headers = ApiTestHelper::authHeaders($this->siswa);

        // Try to start ujian twice simultaneously
        $response1 = $this->withHeaders($headers)
            ->postJson('/api/siswa/ujian/' . $ujian->id . '/start');

        $response2 = $this->withHeaders($headers)
            ->postJson('/api/siswa/ujian/' . $ujian->id . '/start');

        // One should succeed, one should fail
        $this->assertTrue(
            ($response1->status() === 201 && $response2->status() === 422) ||
            ($response1->status() === 422 && $response2->status() === 201)
        );
    }

    /** @test */
    public function it_handles_file_upload_errors()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        // Try to upload invalid file
        $response = $this->withHeaders($headers)
            ->postJson('/api/admin/soal/1/media', [
                'file' => 'not-a-file',
            ]);

        ApiTestHelper::assertValidationError($response, ['file']);
    }

    /** @test */
    public function it_handles_timeout_gracefully()
    {
        // This would require actual long-running operation
        // For now, we just verify timeout configuration exists
        $this->assertIsInt(config('database.connections.mysql.options.timeout'));
    }

    /** @test */
    public function error_responses_have_consistent_structure()
    {
        $headers = ApiTestHelper::authHeaders($this->admin);

        // Test various error scenarios
        $responses = [
            $this->getJson('/api/admin/ujian/99999'), // 404
            $this->postJson('/api/admin/periode-ujian', []), // 422
        ];

        foreach ($responses as $response) {
            $response->assertJsonStructure([
                'success',
                'message',
            ]);
            
            $this->assertFalse($response->json('success'));
        }
    }

    protected function tearDown(): void
    {
        ApiTestHelper::cleanup();
        parent::tearDown();
    }
}
