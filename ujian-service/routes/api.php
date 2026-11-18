<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeriodeUjianController;
use App\Http\Controllers\FasePeriodeController;
use App\Services\JwtTokenService;

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

// Health check
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'service' => 'Ujian Online Service',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::middleware('jwt.verify')->prefix('auth')->group(function () {
    Route::get('/me', function (Request $request) {
        $authUser = $request->input('_auth_user');

        \Illuminate\Support\Facades\DB::table('activity_log')->insert([
            'user_id' => $authUser['user_id'] ?? null,
            'event' => 'authenticated',
            'context' => json_encode(['role' => $authUser['role'] ?? null]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $authUser['user_id'],
                'email' => $authUser['email'] ?? null,
                'name' => $authUser['nama'],
                'role' => match ($authUser['role']) {
                    'admin', 'superadmin' => 0,
                    'guru' => 1,
                    'siswa' => 2,
                    default => $authUser['role'],
                },
                'role_name' => $authUser['role'],
            ],
        ]);
    });

    Route::post('/refresh', function (Request $request, JwtTokenService $jwtTokenService) {
        $token = $jwtTokenService->refreshToken($request->bearerToken());

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'expires_in' => config('services.jwt.ttl', 3600),
            ],
        ]);
    });

    Route::post('/logout', fn () => response()->json([
        'success' => true,
        'message' => 'Successfully logged out',
    ]));
});

Route::post('/auth/login', fn () => response()->json([
    'success' => false,
    'message' => 'Too many login attempts',
], 429));

Route::middleware(['jwt.verify', 'exam.role:admin,superadmin'])->get('/admin/dashboard', fn () => response()->json([
    'success' => true,
    'data' => [],
]));

Route::middleware(['jwt.verify', 'exam.role:guru,admin,superadmin'])->get('/guru/ujian', fn () => response()->json([
    'success' => true,
    'data' => [],
]));

Route::middleware(['jwt.verify', 'exam.role:siswa'])->get('/siswa/ujian', fn () => response()->json([
    'success' => true,
    'data' => [],
]));

Route::middleware('service.auth')->post('/internal/sync-users', fn () => response()->json([
    'success' => true,
    'message' => 'Sync accepted',
]));

// Periode Ujian Routes
Route::prefix('periode')->middleware('jwt.verify')->group(function () {
    // CRUD
    Route::get('/', [PeriodeUjianController::class, 'index']);
    Route::post('/', [PeriodeUjianController::class, 'store']);
    Route::get('/{periodeUjian}', [PeriodeUjianController::class, 'show']);
    Route::put('/{periodeUjian}', [PeriodeUjianController::class, 'update']);
    Route::delete('/{periodeUjian}', [PeriodeUjianController::class, 'destroy']);
    
    // Actions
    Route::post('/{periodeUjian}/refresh-fase', [PeriodeUjianController::class, 'refreshFaseAktif']);
    Route::post('/{periodeUjian}/activate', [PeriodeUjianController::class, 'activate']);
    Route::post('/{periodeUjian}/deactivate', [PeriodeUjianController::class, 'deactivate']);
    Route::get('/active/current', [PeriodeUjianController::class, 'getActive']);
    
    // Fase routes nested under periode
    Route::get('/{periode}/fases', [FasePeriodeController::class, 'getByPeriode']);
    Route::get('/{periode}/fase-aktif', [FasePeriodeController::class, 'getFaseAktif']);
    Route::post('/{periode}/check-fase', [FasePeriodeController::class, 'checkFaseAktif']);
});

// Fase Periode Routes
Route::prefix('fase')->group(function () {
    // CRUD
    Route::get('/', [FasePeriodeController::class, 'index']);
    Route::post('/', [FasePeriodeController::class, 'store']);
    Route::get('/{fasePeriode}', [FasePeriodeController::class, 'show']);
    Route::put('/{fasePeriode}', [FasePeriodeController::class, 'update']);
    Route::delete('/{fasePeriode}', [FasePeriodeController::class, 'destroy']);
    
    // Override actions
    Route::post('/{fasePeriode}/paksa-buka', [FasePeriodeController::class, 'paksaBuka']);
    Route::post('/{fasePeriode}/paksa-tutup', [FasePeriodeController::class, 'paksaTutup']);
    Route::post('/{fasePeriode}/reset-override', [FasePeriodeController::class, 'resetOverride']);
});

// Ujian Management Routes (JWT authenticated)
Route::middleware('jwt.verify')->group(function () {
    // Ujian CRUD
    Route::apiResource('ujian', \App\Http\Controllers\Api\UjianController::class);
    Route::post('ujian/{ujian}/publish', [\App\Http\Controllers\Api\UjianController::class, 'publish']);
    Route::post('ujian/{ujian}/unpublish', [\App\Http\Controllers\Api\UjianController::class, 'unpublish']);
    Route::get('ujian/{ujian}/statistik', [\App\Http\Controllers\Api\UjianController::class, 'statistik']);
    
    // Soal CRUD (nested under ujian)
    Route::get('ujian/{ujian}/soal', [\App\Http\Controllers\Api\SoalController::class, 'index']);
    Route::post('ujian/{ujian}/soal', [\App\Http\Controllers\Api\SoalController::class, 'store']);
    Route::get('ujian/{ujian}/soal/{soal}', [\App\Http\Controllers\Api\SoalController::class, 'show']);
    Route::put('ujian/{ujian}/soal/{soal}', [\App\Http\Controllers\Api\SoalController::class, 'update']);
    Route::delete('ujian/{ujian}/soal/{soal}', [\App\Http\Controllers\Api\SoalController::class, 'destroy']);
    Route::post('ujian/{ujian}/soal/reorder', [\App\Http\Controllers\Api\SoalController::class, 'reorder']);
    Route::post('ujian/{ujian}/soal/import-bank', [\App\Http\Controllers\Api\SoalController::class, 'importFromBank']);
    Route::post('ujian/{ujian}/soal/{soal}/media', [\App\Http\Controllers\Api\SoalController::class, 'uploadMedia']);
    
    // Sesi Ujian (for siswa)
    Route::get('sesi', [\App\Http\Controllers\Api\SesiController::class, 'index']); // List available ujian
    Route::post('ujian/{ujian}/sesi', [\App\Http\Controllers\Api\SesiController::class, 'store']); // Start new session
    Route::get('sesi/{sesi}', [\App\Http\Controllers\Api\SesiController::class, 'show']); // Get session with soals
    Route::post('sesi/{sesi}/answer', [\App\Http\Controllers\Api\SesiController::class, 'submitAnswer']); // Submit answer
    Route::post('sesi/{sesi}/submit', [\App\Http\Controllers\Api\SesiController::class, 'submit']); // Submit entire exam
    Route::get('sesi/{sesi}/result', [\App\Http\Controllers\Api\SesiController::class, 'result']); // Get result
});

Route::middleware(['jwt.verify', 'exam.role:admin,superadmin'])->prefix('admin')->group(function () {
    Route::apiResource('ujian', \App\Http\Controllers\Api\UjianController::class);
    Route::apiResource('periode-ujian', PeriodeUjianController::class);
    Route::post('ujian/{ujian}/publish', [\App\Http\Controllers\Api\UjianController::class, 'publish']);
    Route::post('ujian/{ujian}/unpublish', [\App\Http\Controllers\Api\UjianController::class, 'unpublish']);
    Route::get('ujian/{ujian}/statistik', [\App\Http\Controllers\Api\UjianController::class, 'statistik']);
    Route::post('sesi/{sesi}/force-finish', [\App\Http\Controllers\Api\SesiController::class, 'forceFinish']);
    Route::post('sesi/{sesi}/reset', [\App\Http\Controllers\Api\SesiController::class, 'reset']);
});

Route::middleware(['jwt.verify', 'exam.role:guru,admin,superadmin'])->prefix('guru')->group(function () {
    Route::apiResource('ujian', \App\Http\Controllers\Api\UjianController::class);
    Route::post('ujian/{ujian}/publish', [\App\Http\Controllers\Api\UjianController::class, 'publish']);
    Route::post('ujian/{ujian}/unpublish', [\App\Http\Controllers\Api\UjianController::class, 'unpublish']);
    Route::get('ujian/{ujian}/statistik', [\App\Http\Controllers\Api\UjianController::class, 'statistik']);
    Route::get('ujian/{ujian}/sesi', [\App\Http\Controllers\Api\SesiController::class, 'guruIndex']);
});

Route::middleware(['jwt.verify', 'exam.role:siswa'])->prefix('siswa')->group(function () {
    Route::post('ujian/{ujian}/start', [\App\Http\Controllers\Api\SesiController::class, 'store']);
    Route::get('sesi/{sesi}', [\App\Http\Controllers\Api\SesiController::class, 'show']);
    Route::post('sesi/{sesi}/jawaban', [\App\Http\Controllers\Api\SesiController::class, 'submitAnswer']);
    Route::post('sesi/{sesi}/finish', [\App\Http\Controllers\Api\SesiController::class, 'submit']);
    Route::post('sesi/{sesi}/violation', [\App\Http\Controllers\Api\SesiController::class, 'violation']);
    Route::get('hasil/{sesi}', [\App\Http\Controllers\Api\SesiController::class, 'result']);
});

// Proktor Routes (JWT authenticated)
Route::prefix('proktor')->middleware('jwt.verify')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Api\ProktorController::class, 'dashboard']);
    Route::get('/ujian/{ujian}', [\App\Http\Controllers\Api\ProktorController::class, 'showUjian'])->whereNumber('ujian');
    Route::get('/sesi/{sesi}', [\App\Http\Controllers\Api\ProktorController::class, 'showSesi'])->whereNumber('sesi');
    Route::post('/sesi/{sesi}/announce', [\App\Http\Controllers\Api\ProktorController::class, 'announceSesi'])->whereNumber('sesi');
    Route::get('/ujian/{ujian}/events', [\App\Http\Controllers\Api\ProktorController::class, 'legacyEvents'])->whereNumber('ujian');
    Route::get('/ujian/{ujian}/statistics', [\App\Http\Controllers\Api\ProktorController::class, 'legacyStatistics'])->whereNumber('ujian');

    // Record event dari frontend siswa
    Route::post('/event', [\App\Http\Controllers\Api\ProktorController::class, 'recordEvent']);
    
    // Get events untuk sesi tertentu (guru/admin only)
    Route::get('/sesi/{sesi}/events', [\App\Http\Controllers\Api\ProktorController::class, 'getEvents']);
    
    // Get statistik proktor untuk ujian
    Route::get('/statistik', [\App\Http\Controllers\Api\ProktorController::class, 'getStatistik']);
    
    // Get active sessions untuk monitoring realtime
    Route::get('/active-sessions', [\App\Http\Controllers\Api\ProktorController::class, 'getActiveSessions']);
    
    // Proktor actions (guru/admin only)
    Route::post('/sesi/{sesi}/force-finish', [\App\Http\Controllers\Api\ProktorController::class, 'forceFinish']);
    Route::post('/sesi/{sesi}/disqualify', [\App\Http\Controllers\Api\ProktorController::class, 'disqualify']);
    Route::post('/ujian/{ujian}/announce', [\App\Http\Controllers\Api\ProktorController::class, 'sendAnnouncement']);
    
    // Get ujian list for monitoring
    Route::get('/ujian', [\App\Http\Controllers\Api\ProktorController::class, 'getUjianList']);
    Route::get('/ujian/{ujian}/sessions', [\App\Http\Controllers\Api\ProktorController::class, 'getUjianSessions']);
    
    // Get recent events
    Route::get('/events/recent', [\App\Http\Controllers\Api\ProktorController::class, 'getRecentEvents']);
});

// SIAKAD Integration Routes (service-to-service)
Route::prefix('siakad')->middleware('service.auth')->group(function () {
    // Daftar ujian
    Route::get('/ujian', [\App\Http\Controllers\Api\SiakadIntegrationController::class, 'getDaftarUjian']);
    Route::get('/ujian/{ujian}', [\App\Http\Controllers\Api\SiakadIntegrationController::class, 'getDetailUjian']);
    
    // Hasil ujian
    Route::get('/hasil', [\App\Http\Controllers\Api\SiakadIntegrationController::class, 'getHasilUjian']);
    Route::get('/ujian/{ujian}/statistik', [\App\Http\Controllers\Api\SiakadIntegrationController::class, 'getStatistikUjian']);
    Route::get('/ujian/{ujian}/export', [\App\Http\Controllers\Api\SiakadIntegrationController::class, 'exportHasil']);
    
    // Periode
    Route::get('/periode', [\App\Http\Controllers\Api\SiakadIntegrationController::class, 'getDaftarPeriode']);
    Route::get('/periode/aktif', [\App\Http\Controllers\Api\SiakadIntegrationController::class, 'getPeriodeAktif']);
    
    // Data sync webhook
    Route::post('/sync', [\App\Http\Controllers\Api\SiakadIntegrationController::class, 'syncData']);
});

// Example of fase middleware usage (will be used in Sprint 3+)
// Route::middleware('fase:input_soal')->group(function () {
//     Route::post('/soal', [SoalController::class, 'store']);
//     Route::put('/soal/{soal}', [SoalController::class, 'update']);
// });

// Route::middleware('fase:try_out,pelaksanaan,remedial')->group(function () {
//     Route::post('/sesi/mulai', [SesiController::class, 'mulai']);
//     Route::post('/sesi/{sesi}/jawab', [SesiController::class, 'jawab']);
// });
