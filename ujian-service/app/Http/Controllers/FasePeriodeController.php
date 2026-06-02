<?php

namespace App\Http\Controllers;

use App\Models\FasePeriode;
use App\Models\PeriodeUjian;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FasePeriodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FasePeriode::with('periode');

        // Filter by periode_id
        if ($request->has('periode_id')) {
            $query->where('periode_id', $request->periode_id);
        }

        // Filter by tipe
        if ($request->has('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter by active status
        if ($request->has('aktif') && $request->aktif) {
            $query->aktif();
        }

        // Filter by override status
        if ($request->has('with_override') && $request->with_override) {
            $query->withOverride();
        }

        $fases = $query->orderBy('urutan')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $fases,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periode_ujian,id',
            'tipe' => ['required', Rule::in(['input_soal', 'try_out', 'pelaksanaan', 'remedial', 'rekapitulasi'])],
            'urutan' => 'required|integer|min:1',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after:tgl_mulai',
        ]);

        $fase = FasePeriode::create($validated);

        // Refresh fase aktif di periode
        $fase->periode->refreshFaseAktif();

        return response()->json([
            'success' => true,
            'message' => 'Fase periode berhasil dibuat',
            'data' => $fase->load('periode'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FasePeriode $fasePeriode)
    {
        $fasePeriode->load('periode');

        return response()->json([
            'success' => true,
            'data' => $fasePeriode,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FasePeriode $fasePeriode)
    {
        $validated = $request->validate([
            'tipe' => ['sometimes', 'required', Rule::in(['input_soal', 'try_out', 'pelaksanaan', 'remedial', 'rekapitulasi'])],
            'urutan' => 'sometimes|required|integer|min:1',
            'tgl_mulai' => 'sometimes|required|date',
            'tgl_selesai' => 'sometimes|required|date|after:tgl_mulai',
        ]);

        $fasePeriode->update($validated);

        // Refresh fase aktif di periode
        $fasePeriode->periode->refreshFaseAktif();

        return response()->json([
            'success' => true,
            'message' => 'Fase periode berhasil diupdate',
            'data' => $fasePeriode->load('periode'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FasePeriode $fasePeriode)
    {
        $periode = $fasePeriode->periode;
        $fasePeriode->delete();

        // Refresh fase aktif di periode
        $periode->refreshFaseAktif();

        return response()->json([
            'success' => true,
            'message' => 'Fase periode berhasil dihapus',
        ]);
    }

    /**
     * Override fase: paksa buka
     */
    public function paksaBuka(Request $request, FasePeriode $fasePeriode)
    {
        $validated = $request->validate([
            'override_oleh' => 'required|string|max:255',
        ]);

        $fasePeriode->paksaBuka($validated['override_oleh']);

        return response()->json([
            'success' => true,
            'message' => 'Fase berhasil dipaksa buka',
            'data' => $fasePeriode->fresh()->load('periode'),
        ]);
    }

    /**
     * Override fase: paksa tutup
     */
    public function paksaTutup(Request $request, FasePeriode $fasePeriode)
    {
        $validated = $request->validate([
            'override_oleh' => 'required|string|max:255',
        ]);

        $fasePeriode->paksaTutup($validated['override_oleh']);

        return response()->json([
            'success' => true,
            'message' => 'Fase berhasil dipaksa tutup',
            'data' => $fasePeriode->fresh()->load('periode'),
        ]);
    }

    /**
     * Reset override ke otomatis
     */
    public function resetOverride(FasePeriode $fasePeriode)
    {
        $fasePeriode->resetOverride();

        return response()->json([
            'success' => true,
            'message' => 'Override fase berhasil direset ke otomatis',
            'data' => $fasePeriode->fresh()->load('periode'),
        ]);
    }

    /**
     * Get fase aktif untuk periode tertentu
     */
    public function getFaseAktif(PeriodeUjian $periode)
    {
        $faseAktif = $periode->fases()
            ->aktif()
            ->first();

        if (!$faseAktif) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada fase aktif untuk periode ini',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $faseAktif,
        ]);
    }

    /**
     * Get all fases for a periode
     */
    public function getByPeriode(PeriodeUjian $periode)
    {
        $fases = $periode->fases()
            ->orderBy('urutan')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $fases,
        ]);
    }

    /**
     * Check if specific fase is active
     */
    public function checkFaseAktif(Request $request, PeriodeUjian $periode)
    {
        $validated = $request->validate([
            'tipe' => ['required', Rule::in(['input_soal', 'try_out', 'pelaksanaan', 'remedial', 'rekapitulasi'])],
        ]);

        $isAktif = $periode->isFaseAktif($validated['tipe']);

        return response()->json([
            'success' => true,
            'data' => [
                'tipe' => $validated['tipe'],
                'is_aktif' => $isAktif,
                'fase_aktif_saat_ini' => $periode->fase_aktif,
            ],
        ]);
    }
}