<?php

namespace App\Modules\Ujian\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Root view: shell AdminLTE SIAKAD dengan @inertia di area konten.
     */
    protected $rootView = 'layouts.inertia';

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => match (true) {
                        $user->isAdmin() => 'admin',
                        $user->isGuru() => 'guru',
                        $user->isSiswa() => 'siswa',
                        default => 'lainnya',
                    },
                ] : null,
            ],
            // Nama route aktif, dipakai resources/js/ujian.js untuk menandai menu sidebar.
            'rute' => $request->route()?->getName(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }
}
