<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\JwtTokenService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyJwtToken
{
    public function __construct(private JwtTokenService $jwtTokenService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * Middleware untuk memverifikasi JWT token yang dikirim dari SIAKAD.
     * Token harus dikirim via Authorization header: Bearer <token>
     *
     * Token payload harus berisi:
     * - user_id: ID user dari SIAKAD
     * - role: Role user (siswa, guru, admin)
     * - nama: Nama user
     * - email: Email user (optional)
     * - nis/nip: NIS untuk siswa, NIP untuk guru (optional)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $header = $request->bearerToken()
                ?? ($request->hasSession() ? $request->session()->get('jwt_token') : null)
                ?? $request->cookie('jwt_token')
                ?? $request->query('token');

            if (!$header) {
                if (!$request->expectsJson() && !$request->is('api/*')) {
                    return redirect()->to(rtrim(config('services.siakad.base_url'), '/'));
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak ditemukan',
                    'error_code' => 'TOKEN_ABSENT',
                ], 401);
            }

            $payload = $this->jwtTokenService->verifyToken($header);

            // Validate required fields in payload
            $requiredFields = ['user_id', 'role', 'nama'];
            foreach ($requiredFields as $field) {
                if (!isset($payload[$field])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Token payload tidak lengkap: field '{$field}' tidak ditemukan",
                    ], 401);
                }
            }

            // Validate role
            $allowedRoles = ['siswa', 'guru', 'admin', 'superadmin'];
            if (!in_array($payload['role'], $allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role tidak valid',
                ], 403);
            }

            // Attach user data to request for use in controllers
            $request->merge([
                '_auth_user' => [
                    'user_id' => $payload['user_id'],
                    'role' => $payload['role'],
                    'nama' => $payload['nama'],
                    'email' => $payload['email'] ?? null,
                    'nis' => $payload['nis'] ?? null,
                    'nip' => $payload['nip'] ?? null,
                    'kelas_id' => $payload['kelas_id'] ?? null,
                ],
            ]);

            return $next($request);

        } catch (\Exception $e) {
            if ($e->getMessage() === 'Token expired') {
                if (!$request->expectsJson() && !$request->is('api/*')) {
                    return redirect()->to(rtrim(config('services.siakad.base_url'), '/'));
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Token telah kadaluarsa',
                    'error_code' => 'TOKEN_EXPIRED',
                ], 401);
            }

            if (!$request->expectsJson() && !$request->is('api/*')) {
                return redirect()->to(rtrim(config('services.siakad.base_url'), '/'));
            }

            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid',
                'error_code' => 'TOKEN_INVALID',
            ], 401);
        }
    }
}
