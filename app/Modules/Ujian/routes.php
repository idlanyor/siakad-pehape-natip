<?php

use App\Modules\Ujian\Http\Controllers\BankSoalController;
use App\Modules\Ujian\Http\Controllers\DashboardController;
use App\Modules\Ujian\Http\Controllers\FaseController;
use App\Modules\Ujian\Http\Controllers\HasilController;
use App\Modules\Ujian\Http\Controllers\PeriodeController;
use App\Modules\Ujian\Http\Controllers\SoalUjianController;
use App\Modules\Ujian\Http\Controllers\UjianController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route modul Ujian Online
|--------------------------------------------------------------------------
| Dimuat oleh UjianServiceProvider dengan middleware web + auth + Inertia,
| prefix URL /ujian-online, dan prefix nama "ujian.".
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

// --- Hasil & koreksi ---
Route::get('ujian/{ujian}/hasil', [HasilController::class, 'index'])->name('hasil.index');
Route::get('ujian/{ujian}/hasil/excel', [HasilController::class, 'excel'])->name('hasil.excel');
Route::get('ujian/{ujian}/hasil/pdf', [HasilController::class, 'pdf'])->name('hasil.pdf');
Route::get('sesi/{sesi}/hasil', [HasilController::class, 'sesi'])->name('hasil.sesi');
Route::put('jawaban/{jawaban}/nilai', [HasilController::class, 'nilaiEssay'])->name('hasil.nilai-essay');
