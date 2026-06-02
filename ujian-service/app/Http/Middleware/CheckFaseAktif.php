<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PeriodeUjian;

class CheckFaseAktif
{
    /**
     * Handle an incoming request.
     *
     * Middleware untuk memvalidasi bahwa fase tertentu sedang aktif
     * sebelum mengizinkan akses ke route.
     *
     * Usage di route:
     * Route::get('/soal', [SoalController::class, 'index'])
     *     ->middleware('fase:input_soal');
     *
     * Atau multiple fase:
     * Route::get('/ujian', [UjianController::class, 'index'])
     *     ->middleware('fase:try_out,pelaksanaan,remedial');
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $allowedFases  Fase yang diizinkan (comma-separated)
     */
    public function handle(Request $request, Closure $next, string $allowedFases): Response
    {
        // Parse allowed fases
        $allowedFasesArray = array_map('trim', explode(',', $allowedFases));

        // Get periode_id from request (query, route param, or body)
        $periodeId = $request->input('periode_id') 
                  ?? $request->route('periode_id')
                  ?? $request->route('periode')?->id;

        if (!$periodeId) {
            return response()->json([
                'success' => false,
                'message' => 'Periode ID tidak ditemukan',
            ], 400);
        }

        // Get periode
        $periode = PeriodeUjian::find($periodeId);

        if (!$periode) {
            return response()->json([
                'success' => false,
                'message' => 'Periode tidak ditemukan',
            ], 404);
        }

        // Check if periode is active
        if (!$periode->isAktif()) {
            return response()->json([
                'success' => false,
                'message' => 'Periode tidak aktif',
                'data' => [
                    'periode_status' => $periode->status,
                ],
            ], 403);
        }

        // Refresh fase aktif (untuk memastikan up-to-date)
        $periode->refreshFaseAktif();

        // Check if current fase is in allowed fases
        if (!in_array($periode->fase_aktif, $allowedFasesArray)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Fase tidak sesuai',
                'data' => [
                    'fase_aktif' => $periode->fase_aktif,
                    'fase_diizinkan' => $allowedFasesArray,
                ],
            ], 403);
        }

        // Attach periode to request for use in controller
        $request->merge(['_periode' => $periode]);

        return $next($request);
    }
}