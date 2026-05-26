<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckGuruPermission
{
    public function handle(Request $request, Closure $next, string $menu): Response
    {
        $guru = $request->user()?->guru;

        if (!$guru || !$guru->canAccess($menu)) {
            abort(403, 'Akses menu ini tidak diizinkan.');
        }

        return $next($request);
    }
}
