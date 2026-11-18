<?php

use App\Modules\Ujian\Http\Controllers\BankSoalController;
use App\Modules\Ujian\Http\Controllers\DashboardController;
use App\Modules\Ujian\Http\Controllers\FaseController;
use App\Modules\Ujian\Http\Controllers\HasilController;
use App\Modules\Ujian\Http\Controllers\PeriodeController;
use App\Modules\Ujian\Http\Controllers\ProktorController;
use App\Modules\Ujian\Http\Controllers\Siswa\SesiApiController;
use App\Modules\Ujian\Http\Controllers\Siswa\UjianSiswaController;
use App\Modules\Ujian\Http\Controllers\SoalUjianController;
use App\Modules\Ujian\Http\Controllers\UjianController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route modul Ujian Online
|--------------------------------------------------------------------------
| Dimuat oleh UjianServiceProvider dengan middleware web + auth + Inertia,
| prefix URL /ujian, dan prefix nama "ujian.".
| Otorisasi per aksi lewat Policy di controller.
*/

Route::get('/', DashboardController::class)->name('dashboard');

// --- Periode & fase (admin kelola, guru lihat) ---
Route::resource('periode', PeriodeController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::post('periode/{periode}/fase', [FaseController::class, 'store'])->name('fase.store');
Route::put('fase/{fase}', [FaseController::class, 'update'])->name('fase.update');
Route::delete('fase/{fase}', [FaseController::class, 'destroy'])->name('fase.destroy');
Route::post('fase/{fase}/override', [FaseController::class, 'override'])->name('fase.override');

// --- Bank soal ---
Route::resource('bank-soal', BankSoalController::class)->except('show')
    ->parameters(['bank-soal' => 'bankSoal']);

// --- Ujian ---
Route::resource('ujian', UjianController::class);
Route::post('ujian/{ujian}/status', [UjianController::class, 'status'])->name('ujian.status');
Route::put('ujian/{ujian}/pengawas', [UjianController::class, 'pengawas'])->name('ujian.pengawas');

// --- Penyusunan soal ujian ---
Route::get('ujian/{ujian}/soal', [SoalUjianController::class, 'index'])->name('soal.index');
Route::post('ujian/{ujian}/soal', [SoalUjianController::class, 'store'])->name('soal.store');
Route::post('ujian/{ujian}/soal/dari-bank', [SoalUjianController::class, 'dariBank'])->name('soal.dari-bank');
Route::put('ujian/{ujian}/soal/urutan', [SoalUjianController::class, 'urutan'])->name('soal.urutan');
Route::put('soal/{soal}', [SoalUjianController::class, 'update'])->name('soal.update');
Route::delete('soal/{soal}', [SoalUjianController::class, 'destroy'])->name('soal.destroy');

// --- Siswa: daftar, mengerjakan, hasil ---
Route::prefix('saya')->name('siswa.')->group(function () {
    Route::get('/', [UjianSiswaController::class, 'index'])->name('index');
    Route::post('ujian/{ujian}/mulai', [UjianSiswaController::class, 'mulai'])->name('mulai');
    Route::get('sesi/{sesi}', [UjianSiswaController::class, 'kerjakan'])->name('kerjakan');
    Route::post('sesi/{sesi}/selesai', [UjianSiswaController::class, 'selesai'])->name('selesai');
    Route::get('sesi/{sesi}/hasil', [UjianSiswaController::class, 'hasil'])->name('hasil');

    // JSON (autosave, pelanggaran, heartbeat). Dibatasi per pengguna.
    Route::middleware('throttle:ujian-siswa')->group(function () {
        Route::post('sesi/{sesi}/jawab', [SesiApiController::class, 'jawab'])->name('jawab');
        Route::post('sesi/{sesi}/pelanggaran', [SesiApiController::class, 'pelanggaran'])->name('pelanggaran');
        Route::post('sesi/{sesi}/ping', [SesiApiController::class, 'ping'])->name('ping');
    });
});

// --- Pengawasan (proktor) ---
Route::prefix('proktor')->name('proktor.')->group(function () {
    Route::get('/', [ProktorController::class, 'index'])->name('index');
    Route::get('ujian/{ujian}', [ProktorController::class, 'monitor'])->name('monitor');
    Route::post('sesi/{sesi}/paksa-selesai', [ProktorController::class, 'paksaSelesai'])->name('paksa-selesai');
    Route::post('sesi/{sesi}/diskualifikasi', [ProktorController::class, 'diskualifikasi'])->name('diskualifikasi');
    Route::post('sesi/{sesi}/reset', [ProktorController::class, 'reset'])->name('reset');
});

// --- Hasil & koreksi ---
Route::get('ujian/{ujian}/hasil', [HasilController::class, 'index'])->name('hasil.index');
Route::get('ujian/{ujian}/hasil/excel', [HasilController::class, 'excel'])->name('hasil.excel');
Route::get('ujian/{ujian}/hasil/pdf', [HasilController::class, 'pdf'])->name('hasil.pdf');
Route::get('sesi/{sesi}/hasil', [HasilController::class, 'sesi'])->name('hasil.sesi');
Route::put('jawaban/{jawaban}/nilai', [HasilController::class, 'nilaiEssay'])->name('hasil.nilai-essay');
