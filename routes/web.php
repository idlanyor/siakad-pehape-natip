<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::get('/dashboard', function () {
    if (auth()->user()->role == 1) {
        return redirect()->route('dosen.dashboard');
    }
    return redirect()->route('siswa.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:2'])->prefix('siswa')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\SiswaController::class, 'dashboard'])->name('siswa.dashboard');
    Route::get('/matkul', [\App\Http\Controllers\SiswaController::class, 'matkul'])->name('siswa.matkul');
});

Route::middleware(['auth', 'role:1'])->prefix('dosen')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DosenController::class, 'index'])->name('dosen.dashboard');
    Route::get('/matkul', [\App\Http\Controllers\DosenController::class, 'matkul'])->name('dosen.matkul');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
