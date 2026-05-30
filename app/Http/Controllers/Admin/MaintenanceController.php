<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class MaintenanceController extends Controller
{
    /**
     * Cek status maintenance saat ini.
     */
    public function status()
    {
        $isDown = app()->isDownForMaintenance();
        $secret = $isDown ? $this->getBypassSecret() : null;

        return response()->json([
            'maintenance' => $isDown,
            'secret'      => $secret,
        ]);
    }

    /**
     * Aktifkan maintenance mode dengan bypass secret.
     */
    public function enable(Request $request)
    {
        if (app()->isDownForMaintenance()) {
            return back()->with('error', 'Aplikasi sudah dalam mode maintenance.');
        }

        $secret = Str::random(16);

        Artisan::call('down', [
            '--secret' => $secret,
            '--render' => 'errors::503',
        ]);

        return back()->with('success', "Mode maintenance diaktifkan. Bypass URL: " . url("/?secret={$secret}"));
    }

    /**
     * Nonaktifkan maintenance mode.
     */
    public function disable()
    {
        if (! app()->isDownForMaintenance()) {
            return back()->with('error', 'Aplikasi tidak dalam mode maintenance.');
        }

        Artisan::call('up');

        return back()->with('success', 'Mode maintenance dinonaktifkan. Aplikasi kembali normal.');
    }

    /**
     * Ambil bypass secret dari file down (Laravel 11 menyimpan payload JSON).
     */
    private function getBypassSecret(): ?string
    {
        $path = storage_path('framework/down');
        if (! file_exists($path)) {
            return null;
        }

        $payload = json_decode(file_get_contents($path), true);
        return $payload['secret'] ?? null;
    }
}
