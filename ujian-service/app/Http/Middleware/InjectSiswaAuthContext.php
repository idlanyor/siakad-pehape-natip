<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InjectSiswaAuthContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('siswa')->user();

        if ($user && $user->siswa) {
            $siswa = $user->siswa;
            $request->merge([
                '_auth_user' => [
                    'user_id'  => $siswa->id,
                    'role'     => 'siswa',
                    'kelas_id' => $siswa->kelas_id,
                    'nama'     => $siswa->nama_lengkap,
                    'nis'      => $siswa->nis,
                ],
            ]);
        }

        return $next($request);
    }
}
