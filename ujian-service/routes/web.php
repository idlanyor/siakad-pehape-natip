<?php

use App\Http\Controllers\Auth\SiswaAuthController;
use App\Http\Controllers\Siswa\UjianController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// JWT Handoff from SIAKAD
Route::get('/auth/handoff', [\App\Http\Controllers\Auth\HandoffController::class, 'receive'])->name('auth.handoff');
Route::post('/auth/handoff/logout', [\App\Http\Controllers\Auth\HandoffController::class, 'logout'])->name('auth.handoff.logout');

// Siswa Authentication Routes
Route::prefix('siswa')->name('siswa.')->group(function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [SiswaAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [SiswaAuthController::class, 'login'])->name('login.post');
    });

    // Authenticated routes
    Route::middleware(['auth.siswa', 'siswa.context'])->group(function () {
        Route::get('/dashboard', fn () => redirect()->route('siswa.ujian.index'))->name('dashboard');
        Route::post('/logout', [SiswaAuthController::class, 'logout'])->name('logout');

        // Daftar ujian (HTML)
        Route::get('/ujian', [\App\Http\Controllers\Siswa\UjianController::class, 'index'])->name('ujian.index');

        // Mulai sesi (delegasi ke engine)
        Route::post('/ujian/{ujian}/sesi', [\App\Http\Controllers\Api\SesiController::class, 'store'])->name('sesi.store');

        // Pengerjaan (HTML)
        Route::get('/sesi/{sesi}', [\App\Http\Controllers\Siswa\UjianController::class, 'show'])->name('sesi.show');
        Route::get('/sesi/{sesi}/result', [\App\Http\Controllers\Siswa\UjianController::class, 'result'])->name('sesi.result');

        // Aksi AJAX (delegasi ke engine, JSON)
        Route::post('/sesi/{sesi}/answer', [\App\Http\Controllers\Api\SesiController::class, 'submitAnswer'])->name('sesi.answer');
        Route::post('/sesi/{sesi}/submit', [\App\Http\Controllers\Api\SesiController::class, 'submit'])->name('sesi.submit');
        Route::post('/sesi/{sesi}/violation', [\App\Http\Controllers\Api\SesiController::class, 'violation'])->name('sesi.violation');
    });
});

// Admin/Guru routes (requires JWT from SIAKAD)
Route::middleware('auth.jwt')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Periode routes
    Route::resource('periode', \App\Http\Controllers\PeriodeController::class);
    Route::post('periode/{id}/activate', [\App\Http\Controllers\PeriodeController::class, 'activate'])->name('periode.activate');

    // Fase routes
    Route::post('periode/{periode}/fase', [\App\Http\Controllers\FaseController::class, 'store'])->name('fase.store');
    Route::put('fase/{id}', [\App\Http\Controllers\FaseController::class, 'update'])->name('fase.update');
    Route::delete('fase/{id}', [\App\Http\Controllers\FaseController::class, 'destroy'])->name('fase.destroy');
    Route::post('fase/{id}/activate', [\App\Http\Controllers\FaseController::class, 'activate'])->name('fase.activate');

    // Ujian routes
    Route::resource('ujian', \App\Http\Controllers\UjianController::class);
    Route::post('ujian/{id}/publish', [\App\Http\Controllers\UjianController::class, 'publish'])->name('ujian.publish');
    Route::post('ujian/{id}/unpublish', [\App\Http\Controllers\UjianController::class, 'unpublish'])->name('ujian.unpublish');

    // Soal routes
    Route::get('ujian/{ujian}/soal/create', [\App\Http\Controllers\SoalController::class, 'create'])->name('soal.create');
    Route::post('ujian/{ujian}/soal', [\App\Http\Controllers\SoalController::class, 'store'])->name('soal.store');
    Route::get('ujian/{ujian}/soal/{soal}', [\App\Http\Controllers\SoalController::class, 'show'])->name('soal.show');
    Route::get('ujian/{ujian}/soal/{soal}/edit', [\App\Http\Controllers\SoalController::class, 'edit'])->name('soal.edit');
    Route::put('ujian/{ujian}/soal/{soal}', [\App\Http\Controllers\SoalController::class, 'update'])->name('soal.update');
    Route::delete('ujian/{ujian}/soal/{soal}', [\App\Http\Controllers\SoalController::class, 'destroy'])->name('soal.destroy');

    // Proktor routes
    Route::prefix('proktor')->name('proktor.')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\ProktorController::class, 'dashboard'])->name('dashboard');
        
        // Kelola Akun Siswa
        Route::get('siswa', [\App\Http\Controllers\Proktor\SiswaController::class, 'index'])->name('siswa.index');
        Route::get('siswa/{id}', [\App\Http\Controllers\Proktor\SiswaController::class, 'show'])->name('siswa.show');
        Route::post('siswa/{id}/reset-password', [\App\Http\Controllers\Proktor\SiswaController::class, 'resetPassword'])->name('siswa.reset-password');
        Route::post('siswa/{id}/toggle-active', [\App\Http\Controllers\Proktor\SiswaController::class, 'toggleActive'])->name('siswa.toggle-active');
        Route::post('siswa/{id}/unlock', [\App\Http\Controllers\Proktor\SiswaController::class, 'unlock'])->name('siswa.unlock');
        Route::post('siswa/{id}/change-password', [\App\Http\Controllers\Proktor\SiswaController::class, 'changePassword'])->name('siswa.change-password');
        Route::post('siswa/bulk-reset-password', [\App\Http\Controllers\Proktor\SiswaController::class, 'bulkResetPassword'])->name('siswa.bulk-reset-password');
        Route::post('siswa/bulk-toggle-active', [\App\Http\Controllers\Proktor\SiswaController::class, 'bulkToggleActive'])->name('siswa.bulk-toggle-active');
        Route::get('siswa/export', [\App\Http\Controllers\Proktor\SiswaController::class, 'export'])->name('siswa.export');
        Route::get('siswa/statistics', [\App\Http\Controllers\Proktor\SiswaController::class, 'statistics'])->name('siswa.statistics');
    });
});