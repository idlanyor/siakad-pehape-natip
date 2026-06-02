<?php

namespace App\Services;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class JwtTokenService
{
    /**
     * Generate JWT token untuk user dari SIAKAD
     * 
     * Token ini akan digunakan untuk autentikasi user dari SIAKAD
     * ke service ujian. Token berisi informasi user yang diperlukan.
     *
     * @param array $userData Data user dari SIAKAD
     * @param int $ttl Time to live in seconds (default 3600 = 1 hour)
     * @return string JWT token
     */
    public function generateToken(array $userData, int $ttl = 3600): string
    {
        // Validate required fields
        $requiredFields = ['user_id', 'role', 'nama'];
        foreach ($requiredFields as $field) {
            if (!isset($userData[$field])) {
                throw new \InvalidArgumentException("Field '{$field}' is required");
            }
        }

        // Validate role
        $allowedRoles = ['siswa', 'guru', 'admin', 'superadmin'];
        if (!in_array($userData['role'], $allowedRoles)) {
            throw new \InvalidArgumentException("Invalid role: {$userData['role']}");
        }

        // Prepare payload
        $userPayload = [
            'user_id' => $userData['user_id'],
            'role' => $userData['role'],
            'nama' => $userData['nama'],
            'email' => $userData['email'] ?? null,
            'nis' => $userData['nis'] ?? null,
            'nip' => $userData['nip'] ?? null,
            'kelas_id' => $userData['kelas_id'] ?? null,
            'issued_by' => 'siakad',
        ];

        $payload = [
            'iss' => config('app.url'),
            'sub' => $userData['user_id'],
            'iat' => time(),
            'exp' => time() + $ttl,
            'data' => array_filter($userPayload, fn ($value) => $value !== null),
        ];

        // Generate token manually (compatible with SIAKAD integration)
        $token = $this->encodeToken($payload);

        Log::info('JWT token generated', [
            'user_id' => $userData['user_id'],
            'role' => $userData['role'],
        ]);

        return $token;
    }

    /**
     * Encode payload to JWT token
     *
     * @param array $payload
     * @return string
     */
    private function encodeToken(array $payload): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, config('services.jwt.secret'), true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Decode JWT token
     *
     * @param string $token
     * @return array|null
     */
    private function decodeToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;

        // Verify signature
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, config('services.jwt.secret'), true);
        $base64UrlSignatureCheck = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if ($base64UrlSignature !== $base64UrlSignatureCheck) {
            return null;
        }

        // Decode payload
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64UrlPayload)), true);

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new \Exception('Token expired');
        }

        return $payload;
    }

    /**
     * Verify dan decode JWT token
     *
     * @param string $token JWT token
     * @return array Payload jika valid
     * @throws \Exception jika token invalid
     */
    public function verifyToken(string $token): array
    {
        try {
            $payload = $this->decodeToken($token);
            
            if (!$payload) {
                throw new \Exception('Invalid token');
            }
            
            return $payload['data'] ?? $payload;
        } catch (\Exception $e) {
            Log::warning('JWT token verification failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Refresh JWT token
     *
     * @param string $token Old JWT token
     * @return string New JWT token
     */
    public function refreshToken(string $token): string
    {
        try {
            $payload = $this->decodeToken($token);
            
            if (!$payload) {
                throw new \Exception('Invalid token');
            }
            
            // Generate new token with same user data
            $userData = $payload['data'] ?? $payload;
            $newToken = $this->generateToken($userData);
            
            Log::info('JWT token refreshed');
            
            return $newToken;
        } catch (\Exception $e) {
            Log::error('JWT token refresh failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Invalidate JWT token (logout)
     *
     * @param string $token JWT token
     * @return bool Success status
     */
    public function invalidateToken(string $token): bool
    {
        try {
            JWTAuth::setToken($token);
            JWTAuth::invalidate();
            
            Log::info('JWT token invalidated');
            
            return true;
        } catch (\Exception $e) {
            Log::error('JWT token invalidation failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get user data from token
     *
     * @param string $token JWT token
     * @return array|null User data jika valid, null jika invalid
     */
    public function getUserFromToken(string $token): ?array
    {
        try {
            $payload = $this->verifyToken($token);
            
            return [
                'user_id' => $payload['user_id'] ?? null,
                'role' => $payload['role'] ?? null,
                'nama' => $payload['nama'] ?? null,
                'email' => $payload['email'] ?? null,
                'nis' => $payload['nis'] ?? null,
                'nip' => $payload['nip'] ?? null,
                'kelas_id' => $payload['kelas_id'] ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate service-to-service token
     * Token khusus untuk komunikasi antar service (SIAKAD <-> Ujian Service)
     *
     * @param string $serviceName Nama service yang request
     * @return string Service token (HMAC hash)
     */
    public function generateServiceToken(string $serviceName = 'siakad-service'): string
    {
        $token = hash_hmac('sha256', $serviceName, config('services.siakad.service_key'));

        Log::info('Service-to-service token generated', [
            'service' => $serviceName,
        ]);

        return $token;
    }

    /**
     * Verify service-to-service token
     *
     * @param string $token Service token
     * @return bool Valid atau tidak
     */
    public function verifyServiceToken(string $token): bool
    {
        $expectedToken = $this->generateServiceToken();
        return hash_equals($expectedToken, $token);
    }
}
