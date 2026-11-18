<?php

namespace App\Modules\Ujian;

use App\Modules\Ujian\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Modul Ujian Online (CBT).
 *
 * Seluruh fitur ujian hidup di dalam namespace App\Modules\Ujian. Provider ini
 * mendaftarkan route dan migration modul. Middleware Inertia hanya dipasang
 * pada grup route ujian, sehingga halaman Blade SIAKAD lainnya tidak tersentuh.
 */
class UjianServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/ujian'));

        if (! $this->app->routesAreCached()) {
            Route::middleware(['web', 'auth', HandleInertiaRequests::class])
                ->prefix('ujian-online')
                ->name('ujian.')
                ->group(__DIR__.'/routes.php');
        }
    }
}
