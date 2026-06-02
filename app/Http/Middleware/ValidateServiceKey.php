<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateServiceKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $serviceKey = $request->header('X-Service-Key');
        $expectedKey = config('services.ujian.service_key');

        if (!$serviceKey || !hash_equals($expectedKey, $serviceKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing service key',
            ], 401);
        }

        return $next($request);
    }
}
