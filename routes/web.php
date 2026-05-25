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
    if (auth()->user()->role == 0) {
        return redirect()->route('admin.dashboard');
    }
    if (auth()->user()->role == 1) {
        return redirect()->route('dosen.dashboard');
    }
    return redirect()->route('siswa.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:0'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');

    Route::resource('jurusan', \App\Http\Controllers\Admin\JurusanController::class);
    Route::resource('kelas', \App\Http\Controllers\Admin\KelasController::class)->parameters(['kelas' => 'kela']);
    Route::resource('mapel', \App\Http\Controllers\Admin\MapelController::class);
    Route::resource('guru', \App\Http\Controllers\Admin\GuruController::class);
    Route::resource('siswa', \App\Http\Controllers\Admin\SiswaAdminController::class);

    Route::resource('jadwal', \App\Http\Controllers\Admin\JadwalController::class);

    Route::resource('tahun-ajaran', \App\Http\Controllers\Admin\TahunAjaranController::class)->parameters(['tahun-ajaran' => 'tahunAjaran']);
    Route::post('tahun-ajaran/{tahunAjaran}/aktif', [\App\Http\Controllers\Admin\TahunAjaranController::class, 'setAktif'])->name('tahun-ajaran.aktif');
    Route::post('semester/{semester}/aktif', [\App\Http\Controllers\Admin\TahunAjaranController::class, 'setSemesterAktif'])->name('semester.aktif');
});

Route::middleware(['auth', 'role:2'])->prefix('siswa')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\SiswaController::class, 'dashboard'])->name('siswa.dashboard');
    Route::get('/jadwal', [\App\Http\Controllers\SiswaController::class, 'jadwal'])->name('siswa.jadwal');
    Route::get('/matkul', [\App\Http\Controllers\SiswaController::class, 'matkul'])->name('siswa.matkul');
});

Route::middleware(['auth', 'role:1'])->prefix('dosen')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DosenController::class, 'index'])->name('dosen.dashboard');
    Route::get('/jadwal', [\App\Http\Controllers\DosenController::class, 'jadwal'])->name('dosen.jadwal');
    Route::get('/matkul', [\App\Http\Controllers\DosenController::class, 'matkul'])->name('dosen.matkul');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
