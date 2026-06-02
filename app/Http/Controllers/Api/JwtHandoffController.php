<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * JWT Token Handoff Controller
 * Generates JWT token for admin/guru to access ujian-service
 */
class JwtHandoffController extends Controller
{
    /**
     * Generate JWT token for authenticated admin/guru
     * This token will be used to authenticate in ujian-service
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateToken(Request $request)
    {
        $user = Auth::user();

        // Only admin (role 0) and guru (role 1) can access ujian-service
        if (!in_array($user->role, [0, 1])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin and guru can access ujian service.'
            ], 403);
        }

        // Get role name
        $roleName = $user->role == 0 ? 'admin' : 'guru';

        // Additional data based on role
        $additionalData = [
            'user_id' => $user->id,
            'username' => $user->username,
            'nama' => $user->nama,
            'role' => $roleName,
            'email' => $user->email,
        ];

        // Add guru-specific data
        if ($user->role == 1 && $user->guru) {
            $additionalData['guru_id'] = $user->guru->id;
            $additionalData['nip'] = $user->guru->nip;
        }

        // Generate JWT token with custom claims
        $token = JWTAuth::customClaims($additionalData)->fromUser($user);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60, // seconds
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nama' => $user->nama,
                    'role' => $roleName,
                    'email' => $user->email
                ]
            ]
        ]);
    }

    /**
     * Redirect to ujian-service with JWT token
     * For web-based handoff
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToUjianService(Request $request)
    {
        $user = Auth::user();

        // Only admin and guru
        if (!in_array($user->role, [0, 1])) {
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki akses ke Ujian Service');
        }

        // Generate token
        $roleName = $user->role == 0 ? 'admin' : 'guru';
        
        $additionalData = [
            'user_id' => $user->id,
            'username' => $user->username,
            'nama' => $user->nama,
            'role' => $roleName,
            'email' => $user->email,
        ];

        if ($user->role == 1 && $user->guru) {
            $additionalData['guru_id'] = $user->guru->id;
            $additionalData['nip'] = $user->guru->nip;
        }

        $token = JWTAuth::customClaims($additionalData)->fromUser($user);

        // Get ujian-service URL
        $ujianServiceUrl = config('services.ujian_service.base_url', 'http://localhost:8001');

        // Redirect with token in query (will be stored in session by ujian-service)
        return redirect($ujianServiceUrl . '/auth/handoff?token=' . $token);
    }

    /**
     * Verify JWT token
     * Called by ujian-service to verify token validity
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyToken(Request $request)
    {
        try {
            $token = $request->bearerToken() ?? $request->input('token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not provided'
                ], 400);
            }

            JWTAuth::setToken($token);
            $payload = JWTAuth::getPayload();

            return response()->json([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'user_id' => $payload->get('user_id'),
                    'username' => $payload->get('username'),
                    'nama' => $payload->get('nama'),
                    'role' => $payload->get('role'),
                    'email' => $payload->get('email'),
                    'expires_at' => $payload->get('exp')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Refresh JWT token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        try {
            $token = $request->bearerToken() ?? $request->input('token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not provided'
                ], 400);
            }

            JWTAuth::setToken($token);
            $newToken = JWTAuth::refresh();

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot refresh token',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
