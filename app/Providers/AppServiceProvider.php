<?php

namespace App\Providers;

use App\Models\Notifikasi;
use App\Models\PesanKontak;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Paksa HTTPS di production (link, asset, form action) — lindungi
        // kredensial & data pembayaran dari sniffing di jaringan.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api-auth', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // Share unread notification count & latest notifications to all views
        View::composer('layouts.partials.navbar', function ($view) {
            if (auth()->check()) {
                $userId = auth()->id();

                $unreadCount = Notifikasi::where('user_id', $userId)
                    ->unread()
                    ->count();

                $notifikasiTerbaru = Notifikasi::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                $view->with(compact('unreadCount', 'notifikasiTerbaru'));

                // Unread kontak count (hanya untuk admin)
                if (auth()->user()->role === 0) {
                    $view->with('unreadKontakCount', PesanKontak::unread()->count());
                }
            }
        });
    }
}
