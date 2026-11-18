<?php

namespace Tests\Feature\Helpers;

use App\Models\User;
use App\Services\JwtTokenService;
use Illuminate\Support\Facades\Hash;

/**
 * API Test Helper
 * 
 * Provides utility methods for API testing including:
 * - JWT token generation
 * - User creation with roles
 * - API request helpers
 * - Response assertions
 */
class ApiTestHelper
{
    /**
     * Create a test user with specified role
     * 
     * @param string $role Role: 'admin', 'guru', 'siswa'
     * @param array $attributes Additional attributes
     * @return User
     */
    public static function createUser(string $role = 'siswa', array $attributes = []): User
    {
        $roleMap = [
            'admin' => 0,
            'guru' => 1,
            'siswa' => 2,
        ];

        $defaults = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => $roleMap[$role] ?? 2,
            'is_active' => true,
        ];

        return User::create(array_merge($defaults, $attributes));
    }

    /**
     * Generate JWT token for user
     * 
     * @param User $user
     * @param array $customClaims Additional claims
     * @return string
     */
    public static function generateToken(User $user, array $customClaims = []): string
    {
        $roleName = match ((int) $user->role) {
            0 => 'admin',
            1 => 'guru',
            2 => 'siswa',
            default => (string) $user->role,
        };

        $claims = array_merge([
            'user_id' => $user->id,
            'email' => $user->email,
            'nama' => $user->name,
            'role' => $roleName,
        ], $customClaims);

        if (array_key_exists('sub', $customClaims) && $customClaims['sub'] === null) {
            unset($claims['user_id']);

            $payload = [
                'iss' => config('app.url'),
                'sub' => null,
                'iat' => time(),
                'exp' => time() + 3600,
                'data' => $claims,
            ];

            $header = self::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
            $body = self::base64UrlEncode(json_encode($payload));
            $signature = self::base64UrlEncode(hash_hmac('sha256', $header . '.' . $body, config('services.jwt.secret'), true));

            return $header . '.' . $body . '.' . $signature;
        }

        if (array_key_exists('exp', $claims) && $claims['exp'] && $claims['exp'] < time()) {
            return app(JwtTokenService::class)->generateToken($claims, -60);
        }

        return app(JwtTokenService::class)->generateToken($claims);
    }

    /**
     * Get authorization header with JWT token
     * 
     * @param User $user
     * @return array
     */
    public static function authHeaders(User $user): array
    {
        $token = self::generateToken($user);
        
        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }

    /**
     * Get service authentication headers
     * 
     * @return array
     */
    public static function serviceHeaders(): array
    {
        return [
            'X-Service-Key' => config('services.siakad.service_key'),
            'Accept' => 'application/json',
        ];
    }

    /**
     * Assert API response structure
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @param array $structure
     * @return void
     */
    public static function assertApiStructure($response, array $structure): void
    {
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => $structure,
        ]);
    }

    /**
     * Assert API success response
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @param int $status
     * @return void
     */
    public static function assertApiSuccess($response, int $status = 200): void
    {
        $response->assertStatus($status)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Assert API error response
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @param int $status
     * @param string|null $message
     * @return void
     */
    public static function assertApiError($response, int $status, ?string $message = null): void
    {
        $response->assertStatus($status)
            ->assertJson([
                'success' => false,
            ]);

        if ($message) {
            $response->assertJson([
                'message' => $message,
            ]);
        }
    }

    /**
     * Assert validation error response
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @param array $fields
     * @return void
     */
    public static function assertValidationError($response, array $fields = []): void
    {
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'message',
                'errors',
            ]);

        if (!empty($fields)) {
            $fieldAliases = [
                'periode_ujian_id' => 'periode_id',
                'mata_pelajaran' => 'mapel_id',
                'durasi' => 'durasi_menit',
                'tanggal_mulai' => 'waktu_mulai',
                'tanggal_selesai' => 'waktu_selesai',
            ];

            foreach ($fields as $field) {
                $response->assertJsonValidationErrors($fieldAliases[$field] ?? $field);
            }
        }
    }

    /**
     * Assert unauthorized response
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @return void
     */
    public static function assertUnauthorized($response): void
    {
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Assert forbidden response
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @return void
     */
    public static function assertForbidden($response): void
    {
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Assert not found response
     * 
     * @param \Illuminate\Testing\TestResponse $response
     * @return void
     */
    public static function assertNotFound($response): void
    {
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Create test data for periode ujian
     * 
     * @param array $attributes
     * @return array
     */
    public static function periodeUjianData(array $attributes = []): array
    {
        $now = now();
        
        $defaults = [
            'nama' => 'Periode Ujian ' . fake()->words(3, true),
            'tahun_ajaran' => '2025/2026',
            'semester' => 'ganjil',
            'status' => 'aktif',
            'fase_aktif' => 'pelaksanaan',
            'fases' => [
                [
                    'tipe' => 'pelaksanaan',
                    'urutan' => 1,
                    'tgl_mulai' => $now->format('Y-m-d H:i:s'),
                    'tgl_selesai' => $now->copy()->addDays(7)->format('Y-m-d H:i:s'),
                ]
            ],
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create test data for ujian
     * 
     * @param int $periodeId
     * @param array $attributes
     * @return array
     */
    public static function ujianData(int $periodeId, array $attributes = []): array
    {
        $now = now();
        
        $defaults = [
            'periode_id' => $periodeId,
            'judul' => 'Ujian ' . fake()->words(3, true),
            'mapel_id' => 1,
            'mapel_nama' => fake()->randomElement(['Matematika', 'Bahasa Indonesia', 'IPA', 'IPS']),
            'kelas_id' => 1,
            'kelas_nama' => fake()->randomElement(['10', '11', '12']),
            'tipe' => 'quiz',
            'durasi_menit' => 90,
            'waktu_mulai' => $now->copy()->addDays(10)->format('Y-m-d H:i:s'),
            'waktu_selesai' => $now->copy()->addDays(10)->addHours(2)->format('Y-m-d H:i:s'),
            'jumlah_soal' => 10,
            'passing_grade' => 75,
            'acak_soal' => true,
            'acak_opsi' => true,
            'status' => 'draft',
            'is_published' => false,
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create test data for soal
     * 
     * @param int $ujianId
     * @param array $attributes
     * @return array
     */
    public static function soalData(int $ujianId, array $attributes = []): array
    {
        $defaults = [
            'ujian_id' => $ujianId,
            'pertanyaan' => fake()->paragraph(),
            'tipe' => 'pilihan_ganda',
            'bobot_nilai' => 10,
            'nomor' => 1,
            'kunci_jawaban' => 'A',
            'opsi_a' => fake()->sentence(),
            'opsi_b' => fake()->sentence(),
            'opsi_c' => fake()->sentence(),
            'opsi_d' => fake()->sentence(),
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Create test data for sesi ujian
     * 
     * @param int $ujianId
     * @param int $siswaId
     * @param array $attributes
     * @return array
     */
    public static function sesiUjianData(int $ujianId, int $siswaId, array $attributes = []): array
    {
        $defaults = [
            'ujian_id' => $ujianId,
            'siswa_id' => $siswaId,
            'waktu_mulai' => null,
            'waktu_selesai' => null,
            'status' => 'belum',
            'siswa_nama' => fake()->name(),
            'siswa_nis' => fake()->numerify('#####'),
            'kelas_id' => 1,
            'is_resmi' => true,
            'fase' => 'pelaksanaan',
            'durasi_detik' => 0,
        ];

        return array_merge($defaults, $attributes);
    }

    /**
     * Mock JWT handoff from SIAKAD
     * 
     * @param User $user
     * @return string
     */
    public static function mockSiakadHandoff(User $user): string
    {
        return self::generateToken($user, [
            'iss' => 'siakad',
            'aud' => 'ujian-service',
            'exp' => now()->addHour()->timestamp,
        ]);
    }

    /**
     * Create multiple test users
     * 
     * @param string $role
     * @param int $count
     * @return \Illuminate\Support\Collection
     */
    public static function createUsers(string $role, int $count): \Illuminate\Support\Collection
    {
        return collect(range(1, $count))->map(function () use ($role) {
            return self::createUser($role);
        });
    }

    /**
     * Clean up test data
     * 
     * @return void
     */
    public static function cleanup(): void
    {
        foreach (['jawaban_siswa', 'sesi_ujian', 'soal_ujian', 'ujian', 'periode_ujian', 'activity_log', 'users'] as $table) {
            if (\Schema::hasTable($table)) {
                \DB::table($table)->delete();
            }
        }
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
