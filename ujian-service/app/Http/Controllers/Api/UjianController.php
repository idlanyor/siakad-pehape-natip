<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\SesiUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UjianController extends Controller
{
    /**
     * Display a listing of ujian with filters
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->normalizeLegacyRequest($request);
        $query = Ujian::query();

        // Filter by periode
        if ($request->filled('periode_id')) {
            $query->where('periode_id', $request->periode_id);
        }

        // Filter by kelas
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Filter by mapel
        if ($request->filled('mapel_id')) {
            $query->where('mapel_id', $request->mapel_id);
        }

        // Filter by guru (from JWT token)
        $authUser = $request->input('_auth_user');
        if ($authUser['role'] === 'guru') {
            $query->where('guru_id', $authUser['user_id']);
        } elseif ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }

        // Filter by tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('waktu_mulai', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('waktu_selesai', '<=', $request->tanggal_selesai);
        }

        // Filter by status
        if ($request->filled('status')) {
            $now = now();
            match ($request->status) {
                'draft' => $query->where('is_published', false),
                'belum_mulai' => $query->where('is_published', true)->where('waktu_mulai', '>', $now),
                'published' => $query->where('status', 'published'),
                'berlangsung' => $query->where('is_published', true)->where('waktu_mulai', '<=', $now)->where('waktu_selesai', '>=', $now),
                'selesai' => $query->where('waktu_selesai', '<', $now),
                default => null,
            };
        }

        // Search by judul
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->input('per_page', 20);
        $ujians = $query->latest('waktu_mulai')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $ujians,
        ]);
    }

    /**
     * Store a newly created ujian
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->normalizeLegacyRequest($request);

        $validated = $request->validate([
            'periode_id' => 'required|exists:periode_ujian,id',
            'kelas_id' => 'required|integer',
            'mapel_id' => 'required|integer',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'durasi_menit' => 'required|integer|min:1',
            'passing_grade' => 'required|numeric|min:0|max:100',
            'jumlah_soal' => 'required|integer|min:1',
            'acak_soal' => 'boolean',
            'acak_opsi' => 'boolean',
            'tampilkan_nilai' => 'boolean',
            'keterangan' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get guru_id from JWT token
            $authUser = $request->input('_auth_user');
            $validated['guru_id'] = $authUser['user_id'];
            $validated['guru_nama'] = $authUser['nama'];

            // Default values
            $validated['is_published'] = false;
            $validated['acak_soal'] = $validated['acak_soal'] ?? false;
            $validated['acak_opsi'] = $validated['acak_opsi'] ?? false;
            $validated['tampilkan_nilai'] = $validated['tampilkan_nilai'] ?? true;

            $ujian = Ujian::create($validated);

            DB::commit();

            Log::info('Ujian created', [
                'ujian_id' => $ujian->id,
                'guru_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil dibuat',
                'data' => $ujian,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create ujian', [
                'error' => $e->getMessage(),
                'guru_id' => $authUser['user_id'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat ujian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified ujian
     *
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Ujian $ujian)
    {
        // Check access
        $authUser = request()->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $ujian->load('periode');

        return response()->json([
            'success' => true,
            'data' => $ujian,
        ]);
    }

    /**
     * Update the specified ujian
     *
     * @param Request $request
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Ujian $ujian)
    {
        $this->normalizeLegacyRequest($request);

        // Check access
        $authUser = $request->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Cannot edit if already has sessions
        if ($ujian->sesiUjians()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengubah ujian yang sudah memiliki sesi',
            ], 422);
        }

        if (($ujian->is_published || $ujian->status === 'published') && $ujian->waktu_mulai && $ujian->waktu_mulai->lte(now())) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengubah ujian yang sudah dimulai',
            ], 422);
        }

        $validated = $request->validate([
            'periode_id' => 'sometimes|exists:periode_ujian,id',
            'kelas_id' => 'sometimes|integer',
            'mapel_id' => 'sometimes|integer',
            'judul' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string',
            'waktu_mulai' => 'sometimes|date',
            'waktu_selesai' => 'sometimes|date|after:waktu_mulai',
            'durasi_menit' => 'sometimes|integer|min:1',
            'passing_grade' => 'sometimes|numeric|min:0|max:100',
            'jumlah_soal' => 'sometimes|integer|min:1',
            'acak_soal' => 'boolean',
            'acak_opsi' => 'boolean',
            'tampilkan_nilai' => 'boolean',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $ujian->update($validated);

            Log::info('Ujian updated', [
                'ujian_id' => $ujian->id,
                'guru_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil diupdate',
                'data' => $ujian->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update ujian', [
                'error' => $e->getMessage(),
                'ujian_id' => $ujian->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate ujian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified ujian
     *
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Ujian $ujian)
    {
        // Check access
        $authUser = request()->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Cannot delete if already has sessions
        if ($ujian->sesiUjians()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus ujian yang sudah memiliki sesi',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete related soal
            $ujian->soals()->delete();
            
            // Delete ujian
            $ujian->delete();

            DB::commit();

            Log::info('Ujian deleted', [
                'ujian_id' => $ujian->id,
                'guru_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil dihapus',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete ujian', [
                'error' => $e->getMessage(),
                'ujian_id' => $ujian->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus ujian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Publish ujian (make it available for students)
     *
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish(Ujian $ujian)
    {
        // Check access
        $authUser = request()->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Validate ujian has soals
        if ($ujian->soals()->count() < $ujian->jumlah_soal) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah soal belum mencukupi. Diperlukan ' . $ujian->jumlah_soal . ' soal.',
            ], 422);
        }

        try {
            $ujian->update(['is_published' => true, 'status' => 'published']);

            Log::info('Ujian published', [
                'ujian_id' => $ujian->id,
                'guru_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil dipublish',
                'data' => $ujian->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to publish ujian', [
                'error' => $e->getMessage(),
                'ujian_id' => $ujian->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal publish ujian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unpublish ujian
     *
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function unpublish(Ujian $ujian)
    {
        // Check access
        $authUser = request()->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $ujian->update(['is_published' => false, 'status' => 'draft']);

            Log::info('Ujian unpublished', [
                'ujian_id' => $ujian->id,
                'guru_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil di-unpublish',
                'data' => $ujian->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to unpublish ujian', [
                'error' => $e->getMessage(),
                'ujian_id' => $ujian->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal unpublish ujian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function normalizeLegacyRequest(Request $request): void
    {
        $aliases = [
            'periode_ujian_id' => 'periode_id',
            'tanggal_mulai' => 'waktu_mulai',
            'tanggal_selesai' => 'waktu_selesai',
            'durasi' => 'durasi_menit',
            'acak_jawaban' => 'acak_opsi',
        ];

        $normalized = [];
        foreach ($aliases as $legacy => $current) {
            if ($request->has($legacy)) {
                $normalized[$current] = $request->input($legacy);
            }
        }

        if ($request->has('mata_pelajaran') && !$request->has('mapel_nama')) {
            $normalized['mapel_nama'] = $request->input('mata_pelajaran');
            $normalized['mapel_id'] = $request->input('mapel_id', 1);
        }

        if ($request->has('kelas') && !$request->has('kelas_nama')) {
            $normalized['kelas_nama'] = $request->input('kelas');
            $normalized['kelas_id'] = $request->input('kelas_id', 1);
        }

        if ($request->has('jenis_ujian') && !$request->has('tipe')) {
            $normalized['tipe'] = match (strtolower((string) $request->input('jenis_ujian'))) {
                'uts' => 'uts',
                'uas' => 'uas',
                'remedial', 'remidi' => 'remidi',
                default => 'quiz',
            };
        }

        if (!empty($normalized)) {
            $request->merge($normalized);
        }
    }

    /**
     * Get statistik for ujian
     *
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistik(Ujian $ujian)
    {
        // Check access
        $authUser = request()->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Get all completed sessions
        $sesis = SesiUjian::where('ujian_id', $ujian->id)
            ->where('is_resmi', true)
            ->whereIn('status', ['selesai', 'timeout'])
            ->whereNotNull('nilai_akhir')
            ->get();

        // Calculate statistics
        $stats = [
            'total_peserta' => $ujian->jumlah_peserta,
            'sudah_mengerjakan' => $sesis->count(),
            'persentase_mengerjakan' => $ujian->jumlah_peserta > 0
                ? round(($sesis->count() / $ujian->jumlah_peserta) * 100, 2)
                : 0,
            'rata_rata' => round($sesis->avg('nilai_akhir') ?? 0, 2),
            'tertinggi' => $sesis->max('nilai_akhir') ?? 0,
            'terendah' => $sesis->min('nilai_akhir') ?? 0,
            'lulus' => $sesis->where('nilai_akhir', '>=', $ujian->passing_grade)->count(),
            'tidak_lulus' => $sesis->where('nilai_akhir', '<', $ujian->passing_grade)->count(),
            'passing_rate' => $sesis->count() > 0
                ? round(($sesis->where('nilai_akhir', '>=', $ujian->passing_grade)->count() / $sesis->count()) * 100, 2)
                : 0,
        ];

        // Distribusi nilai (histogram)
        $distribusi = [
            '0-20' => $sesis->whereBetween('nilai_akhir', [0, 20])->count(),
            '21-40' => $sesis->whereBetween('nilai_akhir', [21, 40])->count(),
            '41-60' => $sesis->whereBetween('nilai_akhir', [41, 60])->count(),
            '61-80' => $sesis->whereBetween('nilai_akhir', [61, 80])->count(),
            '81-100' => $sesis->whereBetween('nilai_akhir', [81, 100])->count(),
        ];

        // Ranking siswa (top 10)
        $ranking = $sesis->sortByDesc('nilai_akhir')->take(10)->map(function($sesi) {
            return [
                'siswa_id' => $sesi->siswa_id,
                'siswa_nama' => $sesi->siswa_nama,
                'siswa_nis' => $sesi->siswa_nis,
                'nilai_akhir' => $sesi->nilai_akhir,
                'waktu_selesai' => $sesi->waktu_selesai,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'ujian' => [
                    'id' => $ujian->id,
                    'judul' => $ujian->judul,
                    'passing_grade' => $ujian->passing_grade,
                ],
                'statistik' => $stats,
                'distribusi' => $distribusi,
                'ranking' => $ranking,
            ],
        ]);
    }
}
