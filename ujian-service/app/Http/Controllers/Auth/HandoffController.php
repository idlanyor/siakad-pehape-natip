<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Handle JWT token handoff from SIAKAD
 * Admin/Guru from SIAKAD will be redirected here with JWT token
 */
class HandoffController extends Controller
{
    /**
     * Receive JWT token from SIAKAD and create session
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function receive(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('siswa.login')
                ->with('error', 'Token tidak valid. Silakan login melalui SIAKAD.');
        }

        try {
            // Verify token with SIAKAD
            $siakadUrl = config('services.siakad.base_url');
            $response = Http::timeout(10)
                ->withToken($token)
                ->post("{$siakadUrl}/api/jwt/verify");

            if (!$response->successful()) {
                throw new \Exception('Token verification failed');
            }

            $data = $response->json();
            
            if (!$data['success']) {
                throw new \Exception($data['message'] ?? 'Invalid token');
            }

            $userData = $data['data'];

            // Verify role is admin or guru
            if (!in_array($userData['role'], ['admin', 'guru'])) {
                throw new \Exception('Unauthorized role');
            }

            // Store token and user data in session
            session([
                'jwt_token' => $token,
                'user_id' => $userData['user_id'],
                'user_role' => $userData['role'],
                'user_nama' => $userData['nama'],
                'user_username' => $userData['username'],
                'user_email' => $userData['email'],
                'token_expires_at' => $userData['expires_at']
            ]);

            Log::info('JWT handoff successful', [
                'user_id' => $userData['user_id'],
                'role' => $userData['role']
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Selamat datang, ' . $userData['nama']);

        } catch (\Exception $e) {
            Log::error('JWT handoff failed', [
                'error' => $e->getMessage()
            ]);

            return redirect()->route('siswa.login')
                ->with('error', 'Gagal memverifikasi token. Silakan login kembali melalui SIAKAD.');
        }
    }

    /**
     * Logout and redirect back to SIAKAD
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $request->session()->flush();

        $siakadUrl = config('services.siakad.base_url');
        
        return redirect($siakadUrl . '/logout')
            ->with('success', 'Anda telah logout dari Ujian Service');
    }
}
