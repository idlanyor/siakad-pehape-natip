<?php

use App\Modules\Ujian\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route modul Ujian Online
|--------------------------------------------------------------------------
| Dimuat oleh UjianServiceProvider dengan middleware web + auth + Inertia,
| prefix URL /ujian-online, dan prefix nama "ujian.".
*/

Route::get('/', DashboardController::class)->name('dashboard');
