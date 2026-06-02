<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\JwtTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JwtTokenHandoffTest extends TestCase
{
    use RefreshDatabase;

    protected JwtTokenService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = app(JwtTokenService::class);
    }

    /** @test */
    public function it_can_generate_jwt_token_for_user()
    {
        $userData = [
            'user_id' => 1,
            'nama' => 'Test Siswa',
            'role' => 'siswa',
            'kelas_id' => 10,
            'nis' => '12345',
        ];

        $token = $this->jwtService->generateToken($userData);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        
        // Token should have 3 parts separated by dots
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);
    }

    /** @test */
    public function it_can_verify_valid_jwt_token()
    {
        $userData = [
            'user_id' => 1,
            'nama' => 'Test Siswa',
            'role' => 'siswa',
            'kelas_id' => 10,
        ];

        $token = $this->jwtService->generateToken($userData);
        $decoded = $this->jwtService->verifyToken($token);

        $this->assertIsArray($decoded);
        $this->assertEquals(1, $decoded['user_id']);
        $this->assertEquals('Test Siswa', $decoded['nama']);
        $this->assertEquals('siswa', $decoded['role']);
        $this->assertEquals(10, $decoded['kelas_id']);
    }

    /** @test */
    public function it_rejects_invalid_jwt_token()
    {
        $invalidToken = 'invalid.token.here';

        $this->expectException(\Exception::class);
        $this->jwtService->verifyToken($invalidToken);
    }

    /** @test */
    public function it_rejects_expired_jwt_token()
    {
        $userData = [
            'user_id' => 1,
            'nama' => 'Test Siswa',
            'role' => 'siswa',
        ];

        // Generate token with -1 hour expiry (already expired)
        $token = $this->jwtService->generateToken($userData, -3600);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token expired');
        $this->jwtService->verifyToken($token);
    }

    /** @test */
    public function it_can_refresh_jwt_token()
    {
        $userData = [
            'user_id' => 1,
            'nama' => 'Test Siswa',
            'role' => 'siswa',
        ];

        $oldToken = $this->jwtService->generateToken($userData);
        sleep(1); // Wait 1 second to ensure different timestamp
        $newToken = $this->jwtService->refreshToken($oldToken);

        $this->assertIsString($newToken);
        $this->assertNotEquals($oldToken, $newToken);

        // Both tokens should decode to same user data
        $oldDecoded = $this->jwtService->verifyToken($oldToken);
        $newDecoded = $this->jwtService->verifyToken($newToken);

        $this->assertEquals($oldDecoded['user_id'], $newDecoded['user_id']);
        $this->assertEquals($oldDecoded['nama'], $newDecoded['nama']);
    }

    /** @test */
    public function it_can_extract_user_from_token()
    {
        $userData = [
            'user_id' => 1,
            'nama' => 'Test Siswa',
            'role' => 'siswa',
            'kelas_id' => 10,
        ];

        $token = $this->jwtService->generateToken($userData);
        $extractedUser = $this->jwtService->getUserFromToken($token);

        $this->assertEquals($userData['user_id'], $extractedUser['user_id']);
        $this->assertEquals($userData['nama'], $extractedUser['nama']);
        $this->assertEquals($userData['role'], $extractedUser['role']);
        $this->assertEquals($userData['kelas_id'], $extractedUser['kelas_id']);
    }

    /** @test */
    public function jwt_middleware_allows_valid_token()
    {
        $response = $this->authenticatedRequest('GET', '/api/periode', [], [
            'user_id' => 1,
            'nama' => 'Test Admin',
            'role' => 'admin',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function jwt_middleware_rejects_missing_token()
    {
        $response = $this->json('GET', '/api/periode');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Token tidak ditemukan',
            ]);
    }

    /** @test */
    public function jwt_middleware_rejects_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid.token.here',
            'Accept' => 'application/json',
        ])->json('GET', '/api/periode');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_can_generate_service_token()
    {
        $token = $this->jwtService->generateServiceToken();

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    /** @test */
    public function it_can_verify_service_token()
    {
        $token = $this->jwtService->generateServiceToken();
        $isValid = $this->jwtService->verifyServiceToken($token);

        $this->assertTrue($isValid);
    }

    /** @test */
    public function it_rejects_invalid_service_token()
    {
        $invalidToken = 'invalid-service-token';
        $isValid = $this->jwtService->verifyServiceToken($invalidToken);

        $this->assertFalse($isValid);
    }

    /** @test */
    public function service_middleware_allows_valid_service_key()
    {
        $response = $this->serviceRequest('GET', '/api/siakad/periode');

        $response->assertStatus(200);
    }

    /** @test */
    public function service_middleware_rejects_missing_service_key()
    {
        $response = $this->json('GET', '/api/siakad/periode');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Service key tidak ditemukan',
            ]);
    }

    /** @test */
    public function service_middleware_rejects_invalid_service_key()
    {
        $response = $this->withHeaders([
            'X-Service-Key' => 'invalid-key',
            'Accept' => 'application/json',
        ])->json('GET', '/api/siakad/periode');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Service key tidak valid',
            ]);
    }

    /** @test */
    public function token_contains_correct_user_data_for_siswa()
    {
        $userData = [
            'user_id' => 5,
            'nama' => 'Ahmad Siswa',
            'role' => 'siswa',
            'kelas_id' => 10,
            'nis' => '2024001',
        ];

        $token = $this->jwtService->generateToken($userData);
        $decoded = $this->jwtService->verifyToken($token);

        $this->assertEquals(5, $decoded['user_id']);
        $this->assertEquals('Ahmad Siswa', $decoded['nama']);
        $this->assertEquals('siswa', $decoded['role']);
        $this->assertEquals(10, $decoded['kelas_id']);
        $this->assertEquals('2024001', $decoded['nis']);
    }

    /** @test */
    public function token_contains_correct_user_data_for_guru()
    {
        $userData = [
            'user_id' => 3,
            'nama' => 'Pak Budi Guru',
            'role' => 'guru',
        ];

        $token = $this->jwtService->generateToken($userData);
        $decoded = $this->jwtService->verifyToken($token);

        $this->assertEquals(3, $decoded['user_id']);
        $this->assertEquals('Pak Budi Guru', $decoded['nama']);
        $this->assertEquals('guru', $decoded['role']);
        $this->assertArrayNotHasKey('kelas_id', $decoded);
        $this->assertArrayNotHasKey('nis', $decoded);
    }

    /** @test */
    public function token_contains_correct_user_data_for_admin()
    {
        $userData = [
            'user_id' => 1,
            'nama' => 'Admin User',
            'role' => 'admin',
        ];

        $token = $this->jwtService->generateToken($userData);
        $decoded = $this->jwtService->verifyToken($token);

        $this->assertEquals(1, $decoded['user_id']);
        $this->assertEquals('Admin User', $decoded['nama']);
        $this->assertEquals('admin', $decoded['role']);
    }
}