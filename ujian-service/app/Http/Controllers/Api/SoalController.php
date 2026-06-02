<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\SoalUjian;
use App\Models\BankSoal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SoalController extends Controller
{
    /**
     * Display a listing of soal for specific ujian
     *
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Ujian $ujian)
    {
        // Check access
        $authUser = request()->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $soals = $ujian->soals()
            ->orderBy('nomor_urut')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ujian' => [
                    'id' => $ujian->id,
                    'judul' => $ujian->judul,
                    'jumlah_soal' => $ujian->jumlah_soal,
                ],
                'soals' => $soals,
                'total' => $soals->count(),
            ],
        ]);
    }

    /**
     * Store a newly created soal
     *
     * @param Request $request
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Ujian $ujian)
    {
        // Check access
        $authUser = $request->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Cannot add soal if ujian is published
        if ($ujian->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menambah soal pada ujian yang sudah dipublish',
            ], 422);
        }

        // Check if max soal reached
        if ($ujian->soals()->count() >= $ujian->jumlah_soal) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah soal sudah mencapai maksimal',
            ], 422);
        }

        $validated = $request->validate([
            'tipe' => 'required|in:pilihan_ganda,essay,benar_salah',
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required_if:tipe,pilihan_ganda|nullable|string',
            'opsi_b' => 'required_if:tipe,pilihan_ganda|nullable|string',
            'opsi_c' => 'required_if:tipe,pilihan_ganda|nullable|string',
            'opsi_d' => 'required_if:tipe,pilihan_ganda|nullable|string',
            'opsi_e' => 'nullable|string',
            'kunci_jawaban' => 'required|string',
            'bobot' => 'required|numeric|min:0',
            'pembahasan' => 'nullable|string',
            'media_url' => 'nullable|url',
            'bank_soal_id' => 'nullable|exists:bank_soal,id',
        ]);

        try {
            DB::beginTransaction();

            // Get next nomor_urut
            $nextNomor = $ujian->soals()->max('nomor_urut') + 1;

            $validated['ujian_id'] = $ujian->id;
            $validated['nomor_urut'] = $nextNomor;

            $soal = SoalUjian::create($validated);

            DB::commit();

            Log::info('Soal created', [
                'soal_id' => $soal->id,
                'ujian_id' => $ujian->id,
                'guru_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil ditambahkan',
                'data' => $soal,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create soal', [
                'error' => $e->getMessage(),
                'ujian_id' => $ujian->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan soal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified soal
     *
     * @param Ujian $ujian
     * @param SoalUjian $soal
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Ujian $ujian, SoalUjian $soal)
    {
        // Check access
        $authUser = request()->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Verify soal belongs to ujian
        if ($soal->ujian_id != $ujian->id) {
            return response()->json([
                'success' => false,
                'message' => 'Soal tidak ditemukan dalam ujian ini',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $soal,
        ]);
    }

    /**
     * Update the specified soal
     *
     * @param Request $request
     * @param Ujian $ujian
     * @param SoalUjian $soal
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Ujian $ujian, SoalUjian $soal)
    {
        // Check access
        $authUser = $request->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Verify soal belongs to ujian
        if ($soal->ujian_id != $ujian->id) {
            return response()->json([
                'success' => false,
                'message' => 'Soal tidak ditemukan dalam ujian ini',
            ], 404);
        }

        // Cannot edit if ujian is published
        if ($ujian->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengubah soal pada ujian yang sudah dipublish',
            ], 422);
        }

        $validated = $request->validate([
            'tipe' => 'sometimes|in:pilihan_ganda,essay,benar_salah',
            'pertanyaan' => 'sometimes|string',
            'opsi_a' => 'nullable|string',
            'opsi_b' => 'nullable|string',
            'opsi_c' => 'nullable|string',
            'opsi_d' => 'nullable|string',
            'opsi_e' => 'nullable|string',
            'kunci_jawaban' => 'sometimes|string',
            'bobot' => 'sometimes|numeric|min:0',
            'pembahasan' => 'nullable|string',
            'media_url' => 'nullable|url',
        ]);

        try {
            $soal->update($validated);

            Log::info('Soal updated', [
                'soal_id' => $soal->id,
                'ujian_id' => $ujian->id,
                'guru_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil diupdate',
                'data' => $soal->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update soal', [
                'error' => $e->getMessage(),
                'soal_id' => $soal->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate soal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified soal
     *
     * @param Ujian $ujian
     * @param SoalUjian $soal
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Ujian $ujian, SoalUjian $soal)
    {
        // Check access
        $authUser = request()->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Verify soal belongs to ujian
        if ($soal->ujian_id != $ujian->id) {
            return response()->json([
                'success' => false,
                'message' => 'Soal tidak ditemukan dalam ujian ini',
            ], 404);
        }

        // Cannot delete if ujian is published
        if ($ujian->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus soal pada ujian yang sudah dipublish',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Delete media if exists
            if ($soal->media_url && Storage::exists($soal->media_url)) {
                Storage::delete($soal->media_url);
            }

            $soal->delete();

            // Reorder remaining soals
            $ujian->soals()
                ->where('nomor_urut', '>', $soal->nomor_urut)
                ->decrement('nomor_urut');

            DB::commit();

            Log::info('Soal deleted', [
                'soal_id' => $soal->id,
                'ujian_id' => $ujian->id,
                'guru_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil dihapus',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete soal', [
                'error' => $e->getMessage(),
                'soal_id' => $soal->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus soal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder soals
     *
     * @param Request $request
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request, Ujian $ujian)
    {
        // Check access
        $authUser = $request->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Cannot reorder if ujian is published
        if ($ujian->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengubah urutan soal pada ujian yang sudah dipublish',
            ], 422);
        }

        $validated = $request->validate([
            'soal_ids' => 'required|array',
            'soal_ids.*' => 'required|exists:soal_ujian,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($validated['soal_ids'] as $index => $soalId) {
                SoalUjian::where('id', $soalId)
                    ->where('ujian_id', $ujian->id)
                    ->update(['nomor_urut' => $index + 1]);
            }

            DB::commit();

            Log::info('Soals reordered', [
                'ujian_id' => $ujian->id,
                'guru_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Urutan soal berhasil diupdate',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reorder soals', [
                'error' => $e->getMessage(),
                'ujian_id' => $ujian->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate urutan soal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import soals from bank soal
     *
     * @param Request $request
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function importFromBank(Request $request, Ujian $ujian)
    {
        // Check access
        $authUser = $request->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Cannot import if ujian is published
        if ($ujian->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat import soal pada ujian yang sudah dipublish',
            ], 422);
        }

        $validated = $request->validate([
            'bank_soal_ids' => 'required|array',
            'bank_soal_ids.*' => 'required|exists:bank_soal,id',
        ]);

        try {
            DB::beginTransaction();

            $currentCount = $ujian->soals()->count();
            $availableSlots = $ujian->jumlah_soal - $currentCount;

            if (count($validated['bank_soal_ids']) > $availableSlots) {
                return response()->json([
                    'success' => false,
                    'message' => "Hanya tersisa {$availableSlots} slot soal",
                ], 422);
            }

            $nextNomor = $ujian->soals()->max('nomor_urut') + 1;
            $imported = 0;

            foreach ($validated['bank_soal_ids'] as $bankSoalId) {
                $bankSoal = BankSoal::find($bankSoalId);

                if (!$bankSoal) {
                    continue;
                }

                SoalUjian::create([
                    'ujian_id' => $ujian->id,
                    'bank_soal_id' => $bankSoal->id,
                    'nomor_urut' => $nextNomor++,
                    'tipe' => $bankSoal->tipe,
                    'pertanyaan' => $bankSoal->pertanyaan,
                    'opsi_a' => $bankSoal->opsi_a,
                    'opsi_b' => $bankSoal->opsi_b,
                    'opsi_c' => $bankSoal->opsi_c,
                    'opsi_d' => $bankSoal->opsi_d,
                    'opsi_e' => $bankSoal->opsi_e,
                    'kunci_jawaban' => $bankSoal->kunci_jawaban,
                    'bobot' => $bankSoal->bobot ?? 1,
                    'pembahasan' => $bankSoal->pembahasan,
                    'media_url' => $bankSoal->media_url,
                ]);

                $imported++;
            }

            DB::commit();

            Log::info('Soals imported from bank', [
                'ujian_id' => $ujian->id,
                'imported_count' => $imported,
                'guru_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$imported} soal berhasil diimport dari bank soal",
                'data' => [
                    'imported' => $imported,
                    'total_soal' => $ujian->soals()->count(),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to import soals from bank', [
                'error' => $e->getMessage(),
                'ujian_id' => $ujian->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal import soal dari bank',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload media for soal
     *
     * @param Request $request
     * @param Ujian $ujian
     * @param SoalUjian $soal
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadMedia(Request $request, Ujian $ujian, SoalUjian $soal)
    {
        // Check access
        $authUser = $request->input('_auth_user');
        if ($authUser['role'] === 'guru' && $ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Verify soal belongs to ujian
        if ($soal->ujian_id != $ujian->id) {
            return response()->json([
                'success' => false,
                'message' => 'Soal tidak ditemukan dalam ujian ini',
            ], 404);
        }

        $validated = $request->validate([
            'media' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,mp3,pdf|max:10240', // 10MB
        ]);

        try {
            // Delete old media if exists
            if ($soal->media_url && Storage::exists($soal->media_url)) {
                Storage::delete($soal->media_url);
            }

            // Store new media
            $path = $request->file('media')->store('soal-media', 'public');
            $soal->update(['media_url' => $path]);

            Log::info('Soal media uploaded', [
                'soal_id' => $soal->id,
                'ujian_id' => $ujian->id,
                'guru_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Media berhasil diupload',
                'data' => [
                    'media_url' => Storage::url($path),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to upload soal media', [
                'error' => $e->getMessage(),
                'soal_id' => $soal->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal upload media',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}