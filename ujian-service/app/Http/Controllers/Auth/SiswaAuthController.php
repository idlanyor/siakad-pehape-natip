<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Authentication controller for siswa
 * Login menggunakan akun lokal (user_siswa table)
 */
class SiswaAuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('siswa.login');
    }

    /**
     * Handle siswa login
     * Validates credentials against local user_siswa table
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('username'));
        }

        // Find user by username
        $userSiswa = UserSiswa::where('username', $request->username)->first();

        if (!$userSiswa) {
            return back()
                ->withErrors(['username' => 'Username atau password salah'])
                ->withInput($request->only('username'));
        }

        // Check if account is locked
        if ($userSiswa->isLocked()) {
            $minutesLeft = now()->diffInMinutes($userSiswa->locked_until);
            return back()
                ->withErrors(['username' => "Akun terkunci. Coba lagi dalam {$minutesLeft} menit."])
                ->withInput($request->only('username'));
        }

        // Check if account is active
        if (!$userSiswa->is_active) {
            return back()
                ->withErrors(['username' => 'Akun Anda tidak aktif. Silakan hubungi admin.'])
                ->withInput($request->only('username'));
        }

        // Verify password
        if (!Hash::check($request->password, $userSiswa->password)) {
            $userSiswa->incrementLoginAttempts();
            
            return back()
                ->withErrors(['username' => 'Username atau password salah'])
                ->withInput($request->only('username'));
        }

        // Login successful - update last login and reset attempts
        $userSiswa->updateLastLogin($request->ip());

        // Create session
        Auth::guard('siswa')->login($userSiswa, $request->filled('remember'));

        $request->session()->regenerate();

        // Store additional data in session
        session([
            'siswa_id' => $userSiswa->siswa->id,
            'siswa_nama' => $userSiswa->siswa->nama_lengkap,
            'siswa_kelas' => $userSiswa->siswa->kelas_nama,
            'siswa_nis' => $userSiswa->siswa->nis
        ]);

        return redirect()->intended(route('siswa.ujian.index'))
            ->with('success', 'Login berhasil! Selamat datang, ' . $userSiswa->siswa->nama_lengkap);
    }

    /**
     * Handle siswa logout
     */
    public function logout(Request $request)
    {
        Auth::guard('siswa')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('siswa.login')
            ->with('success', 'Anda telah logout');
    }

    /**
     * API login endpoint
     * Returns token for API access
     */
    public function apiLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by username
        $userSiswa = UserSiswa::where('username', $request->username)->first();

        if (!$userSiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        // Check if account is locked
        if ($userSiswa->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun terkunci. Silakan coba lagi nanti.'
            ], 403);
        }

        // Check if account is active
        if (!$userSiswa->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif'
            ], 403);
        }

        // Verify password
        if (!Hash::check($request->password, $userSiswa->password)) {
            $userSiswa->incrementLoginAttempts();
            
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        // Login successful
        $userSiswa->updateLastLogin($request->ip());

        // Create token (using Laravel Sanctum or similar)
        $token = $userSiswa->createToken('siswa-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'token_type' => 'bearer',
                'siswa' => [
                    'id' => $userSiswa->siswa->id,
                    'nis' => $userSiswa->siswa->nis,
                    'nama' => $userSiswa->siswa->nama_lengkap,
                    'kelas' => $userSiswa->siswa->kelas_nama,
                    'tingkat' => $userSiswa->siswa->tingkat
                ]
            ]
        ]);
    }

    /**
     * Get authenticated siswa info
     */
    public function me(Request $request)
    {
        $userSiswa = Auth::guard('siswa')->user();

        if (!$userSiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $userSiswa->siswa->id,
                'nis' => $userSiswa->siswa->nis,
                'nisn' => $userSiswa->siswa->nisn,
                'nama' => $userSiswa->siswa->nama_lengkap,
                'kelas_id' => $userSiswa->siswa->kelas_id,
                'kelas_nama' => $userSiswa->siswa->kelas_nama,
                'tingkat' => $userSiswa->siswa->tingkat,
                'email' => $userSiswa->siswa->email,
                'status' => $userSiswa->siswa->status,
                'last_login' => $userSiswa->last_login_at
            ]
        ]);
    }
}