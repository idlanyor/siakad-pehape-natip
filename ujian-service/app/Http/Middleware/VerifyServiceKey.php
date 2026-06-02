<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VerifyServiceKey
{
    /**
     * Handle an incoming request.
     *
     * Middleware untuk memverifikasi service-to-service authentication
     * menggunakan X-Service-Key header. Digunakan untuk komunikasi
     * antara SIAKAD dan Ujian Service.
     *
     * Usage di route:
     * Route::middleware('service.auth')->group(function () {
     *     Route::get('/reference', [Controller::class, 'getReferenceData']);
     * });
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $serviceKey = $request->header('X-Service-Key');
        $expectedKey = config('services.siakad.service_key');

        // Check if service key is provided
        if (!$serviceKey) {
            Log::warning('Service-to-service request without X-Service-Key header', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Service key tidak ditemukan',
                'error_code' => 'SERVICE_KEY_MISSING',
            ], 401);
        }

        // Verify service key
        $legacyHmac = hash_hmac('sha256', 'siakad-service', (string) $expectedKey);

        if (!hash_equals((string) $expectedKey, $serviceKey) && !hash_equals($legacyHmac, $serviceKey)) {
            Log::warning('Service-to-service request with invalid X-Service-Key', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Service key tidak valid',
                'error_code' => 'SERVICE_KEY_INVALID',
            ], 401);
        }

        // Attach service info to request
        $request->merge([
            '_service_authenticated' => true,
            '_service_name' => 'siakad',
        ]);

        Log::info('Service-to-service request authenticated', [
            'service' => 'siakad',
            'path' => $request->path(),
        ]);

        return $next($request);
    }
}
