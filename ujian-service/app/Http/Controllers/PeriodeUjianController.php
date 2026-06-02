<?php

namespace App\Http\Controllers;

use App\Models\PeriodeUjian;
use App\Models\FasePeriode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PeriodeUjianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PeriodeUjian::with('fases');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by tahun ajaran
        if ($request->has('tahun_ajaran')) {
            $query->where('tahun_ajaran', $request->tahun_ajaran);
        }

        // Filter by semester
        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        // Search by nama
        if ($request->has('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $periodes = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $periodes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tahun_ajaran' => 'required|string|max:20',
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'status' => ['required', Rule::in(['draft', 'aktif', 'selesai'])],
            'fases' => 'required|array|min:1',
            'fases.*.tipe' => ['required', Rule::in(['input_soal', 'try_out', 'pelaksanaan', 'remedial', 'rekapitulasi'])],
            'fases.*.urutan' => 'required|integer|min:1',
            'fases.*.tgl_mulai' => 'required|date',
            'fases.*.tgl_selesai' => 'required|date|after:fases.*.tgl_mulai',
        ]);

        DB::beginTransaction();
        try {
            // Create periode
            $periode = PeriodeUjian::create([
                'nama' => $validated['nama'],
                'tahun_ajaran' => $validated['tahun_ajaran'],
                'semester' => $validated['semester'],
                'status' => $validated['status'],
            ]);

            // Create fases
            foreach ($validated['fases'] as $faseData) {
                $periode->fases()->create($faseData);
            }

            // Hitung fase aktif
            $periode->refreshFaseAktif();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Periode ujian berhasil dibuat',
                'data' => $periode->load('fases'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat periode ujian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PeriodeUjian $periodeUjian)
    {
        $periodeUjian->load(['fases', 'ujians']);

        return response()->json([
            'success' => true,
            'data' => $periodeUjian,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PeriodeUjian $periodeUjian)
    {
        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'tahun_ajaran' => 'sometimes|required|string|max:20',
            'semester' => ['sometimes', 'required', Rule::in(['ganjil', 'genap'])],
            'status' => ['sometimes', 'required', Rule::in(['draft', 'aktif', 'selesai'])],
        ]);

        $periodeUjian->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Periode ujian berhasil diupdate',
            'data' => $periodeUjian->load('fases'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PeriodeUjian $periodeUjian)
    {
        // Check if periode has ujian
        if ($periodeUjian->ujians()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus periode yang sudah memiliki ujian',
            ], 422);
        }

        $periodeUjian->delete();

        return response()->json([
            'success' => true,
            'message' => 'Periode ujian berhasil dihapus',
        ]);
    }

    /**
     * Refresh fase aktif untuk periode
     */
    public function refreshFaseAktif(PeriodeUjian $periodeUjian)
    {
        $periodeUjian->refreshFaseAktif();

        return response()->json([
            'success' => true,
            'message' => 'Fase aktif berhasil di-refresh',
            'data' => [
                'fase_aktif' => $periodeUjian->fase_aktif,
            ],
        ]);
    }

    /**
     * Activate periode (set status to aktif)
     */
    public function activate(PeriodeUjian $periodeUjian)
    {
        // Deactivate other active periodes with same tahun_ajaran and semester
        PeriodeUjian::where('tahun_ajaran', $periodeUjian->tahun_ajaran)
            ->where('semester', $periodeUjian->semester)
            ->where('status', 'aktif')
            ->where('id', '!=', $periodeUjian->id)
            ->update(['status' => 'selesai']);

        $periodeUjian->update(['status' => 'aktif']);
        $periodeUjian->refreshFaseAktif();

        return response()->json([
            'success' => true,
            'message' => 'Periode ujian berhasil diaktifkan',
            'data' => $periodeUjian->load('fases'),
        ]);
    }

    /**
     * Deactivate periode (set status to selesai)
     */
    public function deactivate(PeriodeUjian $periodeUjian)
    {
        $periodeUjian->update(['status' => 'selesai']);

        return response()->json([
            'success' => true,
            'message' => 'Periode ujian berhasil dinonaktifkan',
            'data' => $periodeUjian->load('fases'),
        ]);
    }

    /**
     * Get active periode
     */
    public function getActive(Request $request)
    {
        $query = PeriodeUjian::aktif()->with('fases');

        if ($request->has('tahun_ajaran')) {
            $query->where('tahun_ajaran', $request->tahun_ajaran);
        }

        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        $periode = $query->first();

        if (!$periode) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada periode aktif',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $periode,
        ]);
    }
}