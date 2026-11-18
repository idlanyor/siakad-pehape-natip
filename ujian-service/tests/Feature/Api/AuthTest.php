<?php

namespace Tests\Feature\Api;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Feature\Helpers\ApiTestHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('app.service_key', 'test-service-key-12345');
    }

    #[Test]
    public function it_can_authenticate_with_valid_jwt_token()
    {
        $user = ApiTestHelper::createUser('siswa');
        $headers = ApiTestHelper::authHeaders($user);

        $response = $this->withHeaders($headers)
            ->getJson('/api/auth/me');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role,
            ],
        ]);
    }

    #[Test]
    public function it_rejects_request_without_token()
    {
        $response = $this->getJson('/api/auth/me');

        ApiTestHelper::assertUnauthorized($response);
    }

    #[Test]
    public function it_rejects_request_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token-12345',
            'Accept' => 'application/json',
        ])->getJson('/api/auth/me');

        ApiTestHelper::assertUnauthorized($response);
    }

    #[Test]
    public function it_rejects_expired_token()
    {
        $user = ApiTestHelper::createUser('siswa');
        
        // Generate token with past expiration
        $token = ApiTestHelper::generateToken($user, [
            'exp' => now()->subHour()->timestamp,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/auth/me');

        ApiTestHelper::assertUnauthorized($response);
    }

    #[Test]
    public function it_can_handle_siakad_jwt_handoff()
    {
        $user = ApiTestHelper::createUser('siswa');
        $token = ApiTestHelper::mockSiakadHandoff($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/auth/me');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);
    }

    #[Test]
    public function it_validates_token_claims()
    {
        $user = ApiTestHelper::createUser('siswa');
        
        // Token without required claims
        $token = ApiTestHelper::generateToken($user, [
            'sub' => null, // Missing subject
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/auth/me');

        ApiTestHelper::assertUnauthorized($response);
    }

    #[Test]
    public function it_can_refresh_token()
    {
        $user = ApiTestHelper::createUser('siswa');
        $headers = ApiTestHelper::authHeaders($user);

        $response = $this->withHeaders($headers)
            ->postJson('/api/auth/refresh');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'token',
                'expires_in',
            ],
        ]);
    }

    #[Test]
    public function it_can_logout()
    {
        $user = ApiTestHelper::createUser('siswa');
        $headers = ApiTestHelper::authHeaders($user);

        $response = $this->withHeaders($headers)
            ->postJson('/api/auth/logout');

        ApiTestHelper::assertApiSuccess($response);
        $response->assertJson([
            'message' => 'Successfully logged out',
        ]);
    }

    #[Test]
    public function admin_can_access_admin_routes()
    {
        $admin = ApiTestHelper::createUser('admin');
        $headers = ApiTestHelper::authHeaders($admin);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
    }

    #[Test]
    public function guru_can_access_guru_routes()
    {
        $guru = ApiTestHelper::createUser('guru');
        $headers = ApiTestHelper::authHeaders($guru);

        $response = $this->withHeaders($headers)
            ->getJson('/api/guru/ujian');

        $response->assertStatus(200);
    }

    #[Test]
    public function siswa_can_access_siswa_routes()
    {
        $siswa = ApiTestHelper::createUser('siswa');
        $headers = ApiTestHelper::authHeaders($siswa);

        $response = $this->withHeaders($headers)
            ->getJson('/api/siswa/ujian');

        $response->assertStatus(200);
    }

    #[Test]
    public function siswa_cannot_access_admin_routes()
    {
        $siswa = ApiTestHelper::createUser('siswa');
        $headers = ApiTestHelper::authHeaders($siswa);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/dashboard');

        ApiTestHelper::assertForbidden($response);
    }

    #[Test]
    public function siswa_cannot_access_guru_routes()
    {
        $siswa = ApiTestHelper::createUser('siswa');
        $headers = ApiTestHelper::authHeaders($siswa);

        $response = $this->withHeaders($headers)
            ->getJson('/api/guru/ujian');

        ApiTestHelper::assertForbidden($response);
    }

    #[Test]
    public function guru_cannot_access_admin_routes()
    {
        $guru = ApiTestHelper::createUser('guru');
        $headers = ApiTestHelper::authHeaders($guru);

        $response = $this->withHeaders($headers)
            ->getJson('/api/admin/dashboard');

        ApiTestHelper::assertForbidden($response);
    }

    #[Test]
    public function it_validates_service_key_for_internal_api()
    {
        $headers = ApiTestHelper::serviceHeaders();

        $response = $this->withHeaders($headers)
            ->postJson('/api/internal/sync-users', [
                'users' => [],
            ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function it_rejects_invalid_service_key()
    {
        $response = $this->withHeaders([
            'X-Service-Key' => 'invalid-key',
            'Accept' => 'application/json',
        ])->postJson('/api/internal/sync-users', [
            'users' => [],
        ]);

        ApiTestHelper::assertUnauthorized($response);
    }

    #[Test]
    public function it_handles_concurrent_requests_with_same_token()
    {
        $user = ApiTestHelper::createUser('siswa');
        $headers = ApiTestHelper::authHeaders($user);

        // Simulate concurrent requests
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->withHeaders($headers)
                ->getJson('/api/auth/me');
        }

        foreach ($responses as $response) {
            ApiTestHelper::assertApiSuccess($response);
        }
    }

    #[Test]
    public function it_rate_limits_authentication_attempts()
    {
        $user = ApiTestHelper::createUser('siswa');

        // Make multiple failed login attempts
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/auth/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        // Next attempt should be rate limited
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    #[Test]
    public function it_logs_authentication_events()
    {
        $user = ApiTestHelper::createUser('siswa');
        $headers = ApiTestHelper::authHeaders($user);

        $this->withHeaders($headers)
            ->getJson('/api/auth/me');

        // Check if authentication event was logged
        $this->assertDatabaseHas('activity_log', [
            'user_id' => $user->id,
            'event' => 'authenticated',
        ]);
    }

    protected function tearDown(): void
    {
        ApiTestHelper::cleanup();
        parent::tearDown();
    }
}
