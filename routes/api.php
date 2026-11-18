<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Siswa\JadwalController as SiswaJadwalController;
use App\Http\Controllers\Api\Siswa\NilaiController as SiswaNilaiController;
use App\Http\Controllers\Api\Siswa\AbsensiController as SiswaAbsensiController;
use App\Http\Controllers\Api\Siswa\RaportController as SiswaRaportController;
use App\Http\Controllers\Api\Siswa\PklController as SiswaPklController;
use App\Http\Controllers\Api\Siswa\KesiswaanController as SiswaKesiswaanController;
use App\Http\Controllers\Api\Siswa\SppController as SiswaSppController;
use App\Http\Controllers\Api\Siswa\PengumumanController as SiswaPengumumanController;
use App\Http\Controllers\Api\Siswa\NotifikasiController as SiswaNotifikasiController;
use App\Http\Controllers\Api\Siswa\MateriController as SiswaMateriController;
use App\Http\Controllers\Api\Siswa\TugasController as SiswaTugasController;
use App\Http\Controllers\Api\Guru\JadwalController as GuruJadwalController;
use App\Http\Controllers\Api\Guru\AbsensiController as GuruAbsensiController;
use App\Http\Controllers\Api\Guru\NilaiController as GuruNilaiController;
use App\Http\Controllers\Api\Guru\PklController as GuruPklController;
use App\Http\Controllers\Api\Guru\MateriController as GuruMateriController;
use App\Http\Controllers\Api\Guru\TugasController as GuruTugasController;

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

// ─── Public & V1 API ────────────────────────────────────────────────────────

Route::prefix('v1')->middleware([\App\Http\Middleware\ForceJsonResponse::class, 'throttle:api'])->group(function () {

    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-auth');

    // ─── Authenticated ──────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/me', [AuthController::class, 'updateProfile']);
        Route::put('/me/password', [AuthController::class, 'updatePassword']);

        // ─── Siswa ──────────────────────────────────────────────────────────
        Route::middleware('role:2')->prefix('siswa')->name('api.siswa.')->group(function () {
            Route::get('/jadwal', [SiswaJadwalController::class, 'index']);
            Route::get('/nilai', [SiswaNilaiController::class, 'index']);
            Route::get('/absensi', [SiswaAbsensiController::class, 'index']);
            Route::get('/raport', [SiswaRaportController::class, 'index']);

            // PKL
            Route::get('/pkl', [SiswaPklController::class, 'index']);
            Route::post('/pkl/jurnal', [SiswaPklController::class, 'storeJurnal']);
            Route::post('/pkl/absensi/masuk', [SiswaPklController::class, 'absensiMasuk']);
            Route::post('/pkl/absensi/pulang', [SiswaPklController::class, 'absensiPulang']);

            // Kesiswaan
            Route::get('/kesiswaan', [SiswaKesiswaanController::class, 'index']);

            // SPP
            Route::get('/spp', [SiswaSppController::class, 'index']);
            Route::get('/spp/{tagihan}', [SiswaSppController::class, 'show']);

            // Pengumuman & Notifikasi
            Route::get('/pengumuman', [SiswaPengumumanController::class, 'index']);
            Route::get('/notifikasi', [SiswaNotifikasiController::class, 'index']);
            Route::post('/notifikasi/{notifikasi}/read', [SiswaNotifikasiController::class, 'markRead']);

            // E-Learning: Materi
            Route::get('/materi', [SiswaMateriController::class, 'index']);
            Route::get('/materi/{materi}', [SiswaMateriController::class, 'show']);

            // E-Learning: Tugas
            Route::get('/tugas', [SiswaTugasController::class, 'index']);
            Route::get('/tugas/{tugas}', [SiswaTugasController::class, 'show']);
            Route::post('/tugas/{tugas}/kumpul', [SiswaTugasController::class, 'kumpul']);
        });

        // ─── Guru ───────────────────────────────────────────────────────────
        Route::middleware('role:1')->prefix('guru')->name('api.guru.')->group(function () {
            Route::get('/jadwal', [GuruJadwalController::class, 'index']);

            // Absensi
            Route::get('/absensi', [GuruAbsensiController::class, 'index']);
            Route::get('/absensi/{jadwal}', [GuruAbsensiController::class, 'show']);
            Route::post('/absensi/{jadwal}', [GuruAbsensiController::class, 'store']);

            // Nilai
            Route::get('/nilai/{jadwal}', [GuruNilaiController::class, 'show']);
            Route::post('/nilai/{jadwal}', [GuruNilaiController::class, 'store']);

            // PKL
            Route::get('/pkl', [GuruPklController::class, 'index']);

            // E-Learning: Materi
            Route::get('/materi/{jadwal}', [GuruMateriController::class, 'show']);
            Route::post('/materi/{jadwal}', [GuruMateriController::class, 'store']);
            Route::delete('/materi/{materi}', [GuruMateriController::class, 'destroy']);

            // E-Learning: Tugas
            Route::get('/tugas/jadwal/{jadwal}', [GuruTugasController::class, 'show']);
            Route::post('/tugas/jadwal/{jadwal}', [GuruTugasController::class, 'store']);
            Route::get('/tugas/{tugas}/pengumpulan', [GuruTugasController::class, 'pengumpulan']);
            Route::post('/pengumpulan/{pengumpulan}/nilai', [GuruTugasController::class, 'beriNilai']);
        });
    });
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