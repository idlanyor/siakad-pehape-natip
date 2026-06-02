<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\SesiUjian;
use App\Models\SoalUjian;
use App\Models\JawabanSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SesiController extends Controller
{
    /**
     * Get available ujian for siswa
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $authUser = $request->input('_auth_user');
        
        if ($authUser['role'] !== 'siswa') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya siswa yang dapat mengakses endpoint ini',
            ], 403);
        }

        $now = now();
        
        // Get available ujian for siswa's kelas
        $ujians = Ujian::where('is_published', true)
            ->where('kelas_id', $authUser['kelas_id'])
            ->where('waktu_mulai', '<=', $now)
            ->where('waktu_selesai', '>=', $now)
            ->with('periode')
            ->get()
            ->map(function($ujian) use ($authUser) {
                // Check if siswa already has session
                $sesi = SesiUjian::where('ujian_id', $ujian->id)
                    ->where('siswa_id', $authUser['user_id'])
                    ->where('is_resmi', true)
                    ->first();

                return [
                    'id' => $ujian->id,
                    'judul' => $ujian->judul,
                    'deskripsi' => $ujian->deskripsi,
                    'mapel_id' => $ujian->mapel_id,
                    'waktu_mulai' => $ujian->waktu_mulai,
                    'waktu_selesai' => $ujian->waktu_selesai,
                    'durasi_menit' => $ujian->durasi_menit,
                    'jumlah_soal' => $ujian->jumlah_soal,
                    'passing_grade' => $ujian->passing_grade,
                    'periode' => $ujian->periode ? [
                        'id' => $ujian->periode->id,
                        'nama' => $ujian->periode->nama,
                    ] : null,
                    'status_sesi' => $sesi ? $sesi->status : null,
                    'sesi_id' => $sesi ? $sesi->id : null,
                    'dapat_dikerjakan' => !$sesi || $sesi->status === 'belum_mulai',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $ujians,
        ]);
    }

    /**
     * Start new exam session
     *
     * @param Request $request
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Ujian $ujian)
    {
        $authUser = $request->input('_auth_user');
        
        if ($authUser['role'] !== 'siswa') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya siswa yang dapat memulai ujian',
            ], 403);
        }

        // Verify ujian is published and available
        if (!$ujian->is_published && $ujian->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Ujian belum dipublish',
            ], 422);
        }

        $now = now();
        if ($now < $ujian->waktu_mulai || $now > $ujian->waktu_selesai) {
            return response()->json([
                'success' => false,
                'message' => 'Ujian tidak dalam waktu pelaksanaan',
            ], 422);
        }

        // Verify siswa's kelas matches ujian
        if (($authUser['kelas_id'] ?? null) && $ujian->kelas_id && $ujian->kelas_id != $authUser['kelas_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Ujian tidak tersedia untuk kelas Anda',
            ], 403);
        }

        // Check if siswa already has active session
        $existingSesi = SesiUjian::where('ujian_id', $ujian->id)
            ->where('siswa_id', $authUser['user_id'])
            ->where('is_resmi', true)
            ->first();

        if ($existingSesi) {
            if (in_array($existingSesi->status, ['berlangsung', 'sedang_mengerjakan', 'belum', 'belum_mulai'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memiliki sesi aktif untuk ujian ini',
                    'data' => [
                        'sesi_id' => $existingSesi->id,
                        'status' => $existingSesi->status,
                    ],
                ], 422);
            }

            if (in_array($existingSesi->status, ['selesai', 'timeout', 'diskualifikasi'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah menyelesaikan ujian ini',
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            // Create new session
            $sesi = SesiUjian::create([
                'ujian_id' => $ujian->id,
                'siswa_id' => $authUser['user_id'],
                'siswa_nama' => $authUser['nama'],
                'siswa_nis' => $authUser['nis'] ?? null,
                'kelas_id' => $authUser['kelas_id'],
                'waktu_mulai' => $now,
                'durasi_detik' => $this->durationSeconds($ujian),
            'status' => 'berlangsung',
                'is_resmi' => true,
                'violation_count' => 0,
            ]);

            // Get soals (randomize if needed)
            $soals = $ujian->soals();
            if ($ujian->acak_soal) {
                $soals = $soals->inRandomOrder();
            } else {
                $soals = $soals->orderBy('nomor');
            }
            $soals = $soals->get();

            // Create jawaban placeholders
            foreach ($soals as $index => $soal) {
                JawabanSiswa::create([
                    'sesi_ujian_id' => $sesi->id,
                    'soal_ujian_id' => $soal->id,
                    'nomor_urut' => $index + 1,
                ]);
            }

            DB::commit();

            Log::info('Exam session started', [
                'sesi_id' => $sesi->id,
                'ujian_id' => $ujian->id,
                'siswa_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sesi ujian berhasil dimulai',
                'data' => [
                    'sesi_id' => $sesi->id,
                    'token_akses' => (string) $sesi->id,
                    'waktu_mulai' => $sesi->waktu_mulai,
                    'waktu_selesai_maksimal' => $sesi->waktu_mulai->copy()->addSeconds($sesi->durasi_detik),
                    'durasi_detik' => $sesi->durasi_detik,
                    'waktu_berakhir' => $sesi->waktu_mulai->copy()->addSeconds($sesi->durasi_detik),
                    'soal' => $soals->values(),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to start exam session', [
                'error' => $e->getMessage(),
                'ujian_id' => $ujian->id,
                'siswa_id' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai sesi ujian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get session details with soals
     *
     * @param SesiUjian $sesi
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(SesiUjian $sesi)
    {
        $authUser = request()->input('_auth_user');

        // Verify ownership
        if ($authUser['role'] === 'siswa' && $sesi->siswa_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Load relations
        $sesi->load(['ujian', 'jawabans.soal']);

        // Check if session is expired
        if (in_array($sesi->status, ['berlangsung', 'sedang_mengerjakan'], true)) {
            $waktuBerakhir = Carbon::parse($sesi->waktu_mulai)->addSeconds($this->sessionDurationSeconds($sesi));
            if (now() > $waktuBerakhir) {
                $this->autoSubmit($sesi);
                $sesi->refresh();
            }
        }

        // Prepare soals data
        $soals = $sesi->jawabans->map(function($jawaban) use ($sesi) {
            $soal = $jawaban->soal;
            
            // Randomize options if needed
            $options = null;
            if ($soal->tipe === 'pilihan_ganda' && $sesi->ujian->acak_opsi) {
                $options = collect([
                    'A' => $soal->opsi_a,
                    'B' => $soal->opsi_b,
                    'C' => $soal->opsi_c,
                    'D' => $soal->opsi_d,
                    'E' => $soal->opsi_e,
                ])->filter()->shuffle();
            } else {
                $options = [
                    'A' => $soal->opsi_a,
                    'B' => $soal->opsi_b,
                    'C' => $soal->opsi_c,
                    'D' => $soal->opsi_d,
                    'E' => $soal->opsi_e,
                ];
            }

            return [
                'id' => $soal->id,
                'nomor' => $soal->nomor,
                'tipe' => $soal->tipe,
                'pertanyaan' => $soal->pertanyaan,
                'opsi' => $options,
                'media_url' => $soal->media_url,
                'jawaban_id' => $jawaban->id,
                'jawaban' => $jawaban->jawaban,
                'is_ragu' => $jawaban->is_ragu,
                // Don't show kunci_jawaban during exam
                'kunci_jawaban' => !in_array($sesi->status, ['berlangsung', 'sedang_mengerjakan'], true) ? $soal->kunci_jawaban : null,
                'is_benar' => $jawaban->is_benar,
                'nilai' => $jawaban->nilai,
            ];
        });

        $waktuBerakhir = Carbon::parse($sesi->waktu_mulai)->addSeconds($this->sessionDurationSeconds($sesi));
        $sisaWaktu = in_array($sesi->status, ['berlangsung', 'sedang_mengerjakan'], true) ? max(0, now()->diffInSeconds($waktuBerakhir, false)) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $sesi->id,
                'ujian_id' => $sesi->ujian_id,
                'ujian_judul' => $sesi->ujian->judul,
                'status' => $sesi->status,
                'waktu_mulai' => $sesi->waktu_mulai,
                'waktu_selesai' => $sesi->waktu_selesai,
                'durasi_detik' => $sesi->durasi_detik,
                'sisa_waktu_detik' => $sisaWaktu,
                'nilai_akhir' => $sesi->nilai_akhir,
                'tab_switch_count' => $sesi->tab_switch_count,
                'sesi' => [
                    'id' => $sesi->id,
                    'ujian_id' => $sesi->ujian_id,
                    'ujian_judul' => $sesi->ujian->judul,
                    'status' => $sesi->status,
                    'waktu_mulai' => $sesi->waktu_mulai,
                    'waktu_selesai' => $sesi->waktu_selesai,
                    'durasi_detik' => $sesi->durasi_detik,
                    'sisa_waktu_detik' => $sisaWaktu,
                    'nilai_akhir' => $sesi->nilai_akhir,
                    'tab_switch_count' => $sesi->tab_switch_count,
                ],
                'soals' => $soals,
                'progress' => [
                    'terjawab' => $sesi->jawabans->whereNotNull('jawaban')->count(),
                    'ragu' => $sesi->jawabans->where('is_ragu', true)->count(),
                    'total' => $sesi->jawabans->count(),
                ],
            ],
        ]);
    }

    /**
     * Submit answer for a soal
     *
     * @param Request $request
     * @param SesiUjian $sesi
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitAnswer(Request $request, SesiUjian $sesi)
    {
        $authUser = $request->input('_auth_user');

        // Verify ownership
        if ($sesi->siswa_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Verify session is active
        if (!in_array($sesi->status, ['berlangsung', 'sedang_mengerjakan'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi ujian tidak aktif',
            ], 422);
        }

        // Check if session is expired
        $waktuBerakhir = Carbon::parse($sesi->waktu_mulai)->addSeconds($this->sessionDurationSeconds($sesi));
        if (now() > $waktuBerakhir) {
            $this->autoSubmit($sesi);
            return response()->json([
                'success' => false,
                'message' => 'Waktu ujian telah habis',
            ], 422);
        }

        $validated = $request->validate([
            'jawaban_id' => 'nullable|exists:jawaban_siswa,id',
            'soal_id' => 'nullable|exists:soal_ujian,id',
            'jawaban' => 'required|string',
            'is_ragu' => 'boolean',
        ]);

        try {
            $jawaban = !empty($validated['jawaban_id'])
                ? JawabanSiswa::find($validated['jawaban_id'])
                : JawabanSiswa::firstOrCreate([
                    'sesi_ujian_id' => $sesi->id,
                    'soal_ujian_id' => $validated['soal_id'],
                ]);

            // Verify jawaban belongs to this sesi
            if ($jawaban->sesi_ujian_id != $sesi->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jawaban tidak ditemukan dalam sesi ini',
                ], 404);
            }

            $jawaban->update([
                'jawaban' => $validated['jawaban'],
                'is_ragu' => $validated['is_ragu'] ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jawaban berhasil disimpan',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to submit answer', [
                'error' => $e->getMessage(),
                'sesi_id' => $sesi->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan jawaban',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit entire exam session
     *
     * @param SesiUjian $sesi
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(SesiUjian $sesi)
    {
        $authUser = request()->input('_auth_user');

        // Verify ownership
        if ($sesi->siswa_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Verify session is active
        if (!in_array($sesi->status, ['berlangsung', 'sedang_mengerjakan'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi ujian tidak aktif',
            ], 422);
        }

        try {
            $this->finalizeSesi($sesi, 'selesai');

            return response()->json([
                'success' => true,
                'message' => 'Ujian berhasil diselesaikan',
                'data' => [
                    'sesi_id' => $sesi->id,
                    'status' => 'selesai',
                    'nilai' => (float) $sesi->fresh()->nilai_akhir,
                    'waktu_selesai' => $sesi->fresh()->waktu_selesai,
                    'nilai_akhir' => $sesi->nilai_akhir,
                    'tampilkan_nilai' => $sesi->ujian->tampilkan_nilai,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to submit exam', [
                'error' => $e->getMessage(),
                'sesi_id' => $sesi->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan ujian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Auto-submit when time is up
     *
     * @param SesiUjian $sesi
     * @return void
     */
    private function autoSubmit(SesiUjian $sesi)
    {
        $this->finalizeSesi($sesi, 'timeout');
        
        Log::info('Exam auto-submitted due to timeout', [
            'sesi_id' => $sesi->id,
            'siswa_id' => $sesi->siswa_id,
        ]);
    }

    /**
     * Finalize sesi and calculate score
     *
     * @param SesiUjian $sesi
     * @param string $status
     * @return void
     */
    private function finalizeSesi(SesiUjian $sesi, string $status)
    {
        DB::beginTransaction();

        try {
            $sesi->load(['jawabans.soal', 'ujian']);

            $totalNilai = 0;
            $totalBobot = 0;

            foreach ($sesi->jawabans as $jawaban) {
                $soal = $jawaban->soal;
                $totalBobot += $soal->bobot;

                if ($soal->tipe === 'essay') {
                    // Essay will be graded manually
                    $jawaban->update([
                        'is_benar' => null,
                        'nilai' => null,
                    ]);
                } else {
                    // Auto-grade pilihan_ganda and benar_salah
                    $isBenar = strtoupper(trim($jawaban->jawaban)) === strtoupper(trim($soal->kunci_jawaban));
                    $nilai = $isBenar ? $soal->bobot : 0;

                    $jawaban->update([
                        'is_benar' => $isBenar,
                        'nilai' => $nilai,
                    ]);

                    $totalNilai += $nilai;
                }
            }

            // Calculate final score (0-100)
            $nilaiAkhir = $totalBobot > 0 ? ($totalNilai / $totalBobot) * 100 : 0;

            $sesi->update([
                'status' => $status,
                'waktu_selesai' => now(),
                'nilai_akhir' => round($nilaiAkhir, 2),
                'nilai' => round($nilaiAkhir, 2),
            ]);

            DB::commit();

            Log::info('Exam session finalized', [
                'sesi_id' => $sesi->id,
                'status' => $status,
                'nilai_akhir' => $nilaiAkhir,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get session result
     *
     * @param SesiUjian $sesi
     * @return \Illuminate\Http\JsonResponse
     */
    public function result(SesiUjian $sesi)
    {
        $authUser = request()->input('_auth_user');

        // Verify ownership or guru access
        if ($authUser['role'] === 'siswa' && $sesi->siswa_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($authUser['role'] === 'guru' && $sesi->ujian->guru_id != $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Verify session is completed
        if (!in_array($sesi->status, ['selesai', 'timeout', 'diskualifikasi'])) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi ujian belum selesai',
            ], 422);
        }

        $sesi->load(['ujian', 'jawabans.soal']);

        $jawabans = $sesi->jawabans->map(function($jawaban) {
            return [
                'nomor' => $jawaban->soal->nomor,
                'pertanyaan' => $jawaban->soal->pertanyaan,
                'tipe' => $jawaban->soal->tipe,
                'jawaban' => $jawaban->jawaban,
                'kunci_jawaban' => $jawaban->soal->kunci_jawaban,
                'is_benar' => $jawaban->is_benar,
                'nilai' => $jawaban->nilai,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'sesi' => [
                    'id' => $sesi->id,
                    'ujian_judul' => $sesi->ujian->judul,
                    'siswa_nama' => $sesi->siswa_nama,
                    'status' => $sesi->status,
                    'waktu_mulai' => $sesi->waktu_mulai,
                    'waktu_selesai' => $sesi->waktu_selesai,
                    'nilai_akhir' => $sesi->nilai_akhir,
                    'passing_grade' => $sesi->ujian->passing_grade,
                    'lulus' => $sesi->nilai_akhir >= $sesi->ujian->passing_grade,
                ],
                'sesi_id' => $sesi->id,
                'nilai' => (float) $sesi->nilai_akhir,
                'status' => $sesi->status,
                'jawabans' => $jawabans,
                'statistik' => [
                    'benar' => $sesi->jawabans->where('is_benar', true)->count(),
                    'salah' => $sesi->jawabans->where('is_benar', false)->count(),
                    'belum_dinilai' => $sesi->jawabans->whereNull('is_benar')->count(),
                ],
            ],
        ]);
    }

    public function guruIndex(Request $request, Ujian $ujian)
    {
        $authUser = $request->input('_auth_user');

        if (($authUser['role'] ?? null) === 'guru' && (int) $ujian->guru_id !== (int) $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => SesiUjian::where('ujian_id', $ujian->id)->paginate(15),
        ]);
    }

    public function forceFinish(Request $request, SesiUjian $sesi)
    {
        $authUser = $request->input('_auth_user');

        if (!in_array($authUser['role'] ?? null, ['admin', 'superadmin'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $sesi->update([
            'status' => 'selesai',
            'waktu_selesai' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'sesi_id' => $sesi->id,
                'status' => 'selesai',
            ],
        ]);
    }

    public function reset(Request $request, SesiUjian $sesi)
    {
        $authUser = $request->input('_auth_user');

        if (!in_array($authUser['role'] ?? null, ['admin', 'superadmin'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $sesi->jawabans()->delete();
        $sesi->update([
            'status' => 'belum_mulai',
            'waktu_mulai' => null,
            'waktu_selesai' => null,
            'nilai_otomatis' => null,
            'nilai_manual' => null,
            'nilai_akhir' => null,
            'nilai' => null,
            'tab_switch_count' => 0,
            'violation_count' => 0,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'sesi_id' => $sesi->id,
                'status' => 'belum_mulai',
            ],
        ]);
    }

    public function violation(Request $request, SesiUjian $sesi)
    {
        $authUser = $request->input('_auth_user');

        if ($sesi->siswa_id != ($authUser['user_id'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $count = $sesi->violation_count + 1;
        $sesi->update([
            'tab_switch_count' => $count,
            'violation_count' => $count,
            'status' => $count >= 5 ? 'diskualifikasi' : $sesi->status,
            'waktu_selesai' => $count >= 5 ? now() : $sesi->waktu_selesai,
            'last_activity' => now(),
        ]);

        DB::table('proktor_events')->insert([
            'sesi_ujian_id' => $sesi->id,
            'event_type' => $request->input('type', 'violation'),
            'description' => 'Violation reported',
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'violation_count' => $count,
                'status' => $sesi->fresh()->status,
            ],
        ]);
    }

    private function durationSeconds(Ujian $ujian): int
    {
        return max((int) $ujian->durasi_menit, 1) * 60;
    }

    private function sessionDurationSeconds(SesiUjian $sesi): int
    {
        return $sesi->durasi_detik > 0 ? (int) $sesi->durasi_detik : $this->durationSeconds($sesi->ujian);
    }
}
