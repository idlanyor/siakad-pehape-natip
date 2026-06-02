<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'SIAKAD',
    ]);
});

// --- Ujian Service Integration ---
Route::prefix('ujian-service')->name('ujian-service.')->group(function () {
    
    // Health check
    Route::get('/health', [
        \App\Http\Controllers\Api\UjianServiceController::class,
        'healthCheck'
    ])->name('health');

    // Reference data endpoints (protected by service key or admin auth)
    Route::middleware(['auth:sanctum', 'role:0'])->group(function () {
        Route::get('/kelas', [
            \App\Http\Controllers\Api\UjianServiceController::class,
            'getKelas'
        ])->name('kelas');

        Route::get('/mapel', [
            \App\Http\Controllers\Api\UjianServiceController::class,
            'getMapel'
        ])->name('mapel');

        Route::get('/siswa/by-kelas', [
            \App\Http\Controllers\Api\UjianServiceController::class,
            'getSiswaByKelas'
        ])->name('siswa.by-kelas');

        Route::get('/siswa/{id}', [
            \App\Http\Controllers\Api\UjianServiceController::class,
            'getSiswaDetail'
        ])->name('siswa.detail');
    });

    // Sync endpoints (called by ujian-service - no auth required for now)
    // TODO: Add service key authentication middleware
    Route::post('/sync/siswa', [
        \App\Http\Controllers\Api\UjianServiceController::class,
        'syncSiswa'
    ])->name('sync.siswa');

    Route::post('/sync/push-updates', [
        \App\Http\Controllers\Api\UjianServiceController::class,
        'pushSiswaUpdates'
    ])->name('sync.push');

    // Verify siswa credentials (for login at ujian-service - no auth required)
    Route::post('/verify-credentials', [
        \App\Http\Controllers\Api\UjianServiceController::class,
        'verifySiswaCredentials'
    ])->name('verify-credentials');
});

// --- JWT Token Handoff for Admin/Guru ---
Route::prefix('jwt')->middleware('auth:sanctum')->name('jwt.')->group(function () {
    // Generate JWT token for ujian-service access
    Route::post('/generate', [
        \App\Http\Controllers\Api\JwtHandoffController::class,
        'generateToken'
    ])->name('generate');

    // Verify token (called by ujian-service)
    Route::post('/verify', [
        \App\Http\Controllers\Api\JwtHandoffController::class,
        'verifyToken'
    ])->name('verify');

    // Refresh token
    Route::post('/refresh', [
        \App\Http\Controllers\Api\JwtHandoffController::class,
        'refreshToken'
    ])->name('refresh');
});

// Web redirect to ujian-service with token
Route::get('/ujian-service/redirect', [
    \App\Http\Controllers\Api\JwtHandoffController::class,
    'redirectToUjianService'
])->middleware('auth:sanctum')->name('ujian-service.redirect');

// Broadcasting authentication for private channels
Route::middleware('auth:api')->post('/broadcasting/auth', function (Request $request) {
    return response()->json([
        'auth' => auth()->user()->id,
    ]);
});