<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    /**
     * Bootstrap aplikasi dengan konfigurasi testing yang dipaksakan.
     *
     * Variabel DB_* dari shell environment bisa saja terbawa ke dalam test
     * lewat $_SERVER, sehingga nilai dari phpunit.xml tidak selalu menang.
     * Agar test tidak pernah menyentuh database production, koneksi di sini
     * dipaksa ke SQLite in-memory.
     */
    public function createApplication(): Application
    {
        $this->forceTestingEnvironment();

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->guardAgainstProductionDatabase($app);

        return $app;
    }

    /**
     * Paksa nilai environment testing pada $_SERVER/$_ENV/getenv.
     *
     * env() membaca $_SERVER lebih dulu, dan $_SERVER diwarisi dari shell.
     * Karena itu penulisan harus dilakukan sebelum aplikasi di-bootstrap.
     */
    protected function forceTestingEnvironment(): void
    {
        $vars = [
            'APP_ENV' => 'testing',
            'APP_DEBUG' => 'true',
            'BCRYPT_ROUNDS' => '4',
            'CACHE_STORE' => 'array',
            'MAIL_MAILER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'array',
            'BROADCAST_CONNECTION' => 'log',
            'JWT_SECRET' => 'test-jwt-secret-key-for-testing-only',
            'SIAKAD_SERVICE_KEY' => 'test-service-key-for-testing',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
            'DB_SOCKET' => '',
            'DB_URL' => '',
        ];

        foreach ($vars as $key => $value) {
            $_SERVER[$key] = $value;
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }

    /**
     * Guard: hentikan test bila koneksi database bukan SQLite in-memory.
     */
    protected function guardAgainstProductionDatabase(Application $app): void
    {
        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new \RuntimeException(
                'Test dibatalkan: koneksi database testing bukan SQLite in-memory '
                ."({$connection} / ".var_export($database, true).').'
            );
        }
    }

    /**
     * Generate JWT token for testing
     */
    protected function generateTestToken(array $userData = []): string
    {
        $defaultData = [
            'user_id' => 1,
            'nama' => 'Test User',
            'role' => 'siswa',
            'kelas_id' => 1,
            'nis' => '12345',
        ];

        $data = array_merge($defaultData, $userData);

        $payload = [
            'iss' => config('app.url'),
            'sub' => $data['user_id'],
            'iat' => time(),
            'exp' => time() + 3600,
            'data' => $data,
        ];

        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader.'.'.$base64UrlPayload, config('services.jwt.secret'), true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader.'.'.$base64UrlPayload.'.'.$base64UrlSignature;
    }

    /**
     * Generate service token for testing
     */
    protected function generateServiceToken(): string
    {
        return hash_hmac('sha256', 'siakad-service', config('services.siakad.service_key'));
    }

    /**
     * Make authenticated request with JWT token
     *
     * @return TestResponse
     */
    protected function authenticatedRequest(string $method, string $uri, array $data = [], array $userData = [])
    {
        $token = $this->generateTestToken($userData);

        return $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->json($method, $uri, $data);
    }

    /**
     * Make service-to-service authenticated request
     *
     * @return TestResponse
     */
    protected function serviceRequest(string $method, string $uri, array $data = [])
    {
        $token = $this->generateServiceToken();

        return $this->withHeaders([
            'X-Service-Key' => $token,
            'Accept' => 'application/json',
        ])->json($method, $uri, $data);
    }
}
