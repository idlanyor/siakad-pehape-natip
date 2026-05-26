<?php

namespace App\Providers;

use App\Models\Notifikasi;
use App\Models\PesanKontak;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
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
