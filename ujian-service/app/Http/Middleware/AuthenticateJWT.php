<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * Middleware to authenticate admin/guru via JWT token from SIAKAD
 * Token is passed from SIAKAD when admin/guru accesses ujian-service
 */
class AuthenticateJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Try to get token from session first (web)
            $token = session('jwt_token');
            
            // If not in session, try Authorization header (API)
            if (!$token) {
                $token = $request->bearerToken();
            }

            if (!$token) {
                throw new JWTException('Token not provided');
            }

            // Set token for JWTAuth
            JWTAuth::setToken($token);
            
            // Authenticate and get payload
            $payload = JWTAuth::getPayload();
            
            // Verify token is from SIAKAD and for admin/guru
            $role = $payload->get('role');
            if (!in_array($role, ['admin', 'guru'])) {
                throw new JWTException('Invalid role');
            }

            // Store user info in request
            $request->merge([
                'auth_user_id' => $payload->get('user_id'),
                'auth_role' => $role,
                'auth_nama' => $payload->get('nama'),
            ]);

            // Store in session for web requests
            if (!$request->expectsJson()) {
                session([
                    'jwt_token' => $token,
                    'user_id' => $payload->get('user_id'),
                    'user_role' => $role,
                    'user_nama' => $payload->get('nama'),
                ]);
            }

            return $next($request);
        } catch (JWTException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: ' . $e->getMessage()
                ], 401);
            }

            // Redirect to SIAKAD login
            $siakadUrl = config('services.siakad.base_url');
            return redirect($siakadUrl . '/login')
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }
    }
}
