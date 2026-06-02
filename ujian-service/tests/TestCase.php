<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Generate JWT token for testing
     *
     * @param array $userData
     * @return string
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

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, config('services.jwt.secret'), true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Generate service token for testing
     *
     * @return string
     */
    protected function generateServiceToken(): string
    {
        return hash_hmac('sha256', 'siakad-service', config('services.siakad.service_key'));
    }

    /**
     * Make authenticated request with JWT token
     *
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param array $userData
     * @return \Illuminate\Testing\TestResponse
     */
    protected function authenticatedRequest(string $method, string $uri, array $data = [], array $userData = [])
    {
        $token = $this->generateTestToken($userData);
        
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->json($method, $uri, $data);
    }

    /**
     * Make service-to-service authenticated request
     *
     * @param string $method
     * @param string $uri
     * @param array $data
     * @return \Illuminate\Testing\TestResponse
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
