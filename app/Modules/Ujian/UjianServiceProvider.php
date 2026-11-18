<?php

namespace App\Modules\Ujian;

use App\Modules\Ujian\Http\Middleware\HandleInertiaRequests;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
    /** @var array<class-string, class-string> */
    private const POLICIES = [
        Models\Ujian::class => Policies\UjianPolicy::class,
        Models\SesiUjian::class => Policies\SesiUjianPolicy::class,
        Models\BankSoal::class => Policies\BankSoalPolicy::class,
        Models\PeriodeUjian::class => Policies\PeriodeUjianPolicy::class,
    ];

    public function boot(): void
    {
        foreach (self::POLICIES as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Autosave + heartbeat + pelanggaran dari satu siswa: cukup longgar untuk
        // menjawab cepat, tetapi menahan skrip yang membanjiri server.
        RateLimiter::for('ujian-siswa', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));

        $this->loadMigrationsFrom(database_path('migrations/ujian'));

        if (! $this->app->routesAreCached()) {
            Route::middleware(['web', 'auth', HandleInertiaRequests::class])
                ->prefix('ujian')
                ->name('ujian.')
                ->group(__DIR__.'/routes.php');
        }
    }
}
