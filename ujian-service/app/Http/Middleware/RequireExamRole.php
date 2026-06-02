<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireExamRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $authUser = $request->input('_auth_user', []);

        if (!in_array($authUser['role'] ?? null, $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        return $next($request);
    }
}
