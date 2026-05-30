<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blokir akses ke wizard installer jika aplikasi sudah terpasang
 * (ditandai file storage/installed). Mencegah re-install yang bisa
 * menimpa konfigurasi / data instance yang sudah berjalan.
 */
class RedirectIfInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (file_exists(storage_path('installed'))) {
            return redirect('/')->with('error', 'Aplikasi sudah terpasang.');
        }

        return $next($request);
    }
}
