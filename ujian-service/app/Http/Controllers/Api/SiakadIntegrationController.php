<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeriodeUjian;
use App\Models\Ujian;
use App\Models\SesiUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiakadIntegrationController extends Controller
{
    /**
     * Get daftar ujian untuk ditampilkan di SIAKAD
     * 
     * Endpoint ini dipanggil oleh SIAKAD untuk mendapatkan daftar ujian
     * yang tersedia untuk kelas/siswa tertentu
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDaftarUjian(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'nullable|exists:periode_ujian,id',
            'kelas_id' => 'nullable|integer',
            'mapel_id' => 'nullable|integer',
            'status' => 'nullable|in:draft,published,selesai',
            'tipe' => 'nullable|in:uts,uas,harian,remedial',
        ]);

        $query = Ujian::with(['periode', 'soals']);

        // Filter by periode
        if (isset($validated['periode_id'])) {
            $query->where('periode_id', $validated['periode_id']);
        } else {
            // Default: hanya periode aktif
            $query->whereHas('periode', function($q) {
                $q->where('status', 'aktif');
            });
        }

        // Filter by kelas
        if (isset($validated['kelas_id'])) {
            $query->where('kelas_id', $validated['kelas_id']);
        }

        // Filter by mapel
        if (isset($validated['mapel_id'])) {
            $query->where('mapel_id', $validated['mapel_id']);
        }

        // Filter by status
        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // Filter by tipe
        if (isset($validated['tipe'])) {
            $query->where('tipe', $validated['tipe']);
        }

        $ujians = $query->orderBy('waktu_mulai', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $ujians,
        ]);
    }

    /**
     * Get detail ujian
     *
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetailUjian(Ujian $ujian)
    {
        $ujian->load(['periode', 'soals']);

        return response()->json([
            'success' => true,
            'data' => $ujian,
        ]);
    }

    /**
     * Get hasil ujian untuk siswa tertentu
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHasilUjian(Request $request)
    {
        $validated = $request->validate([
            'ujian_id' => 'required|exists:ujian,id',
            'siswa_id' => 'nullable|integer',
            'kelas_id' => 'nullable|integer',
        ]);

        $query = SesiUjian::with(['ujian', 'jawabans'])
            ->where('ujian_id', $validated['ujian_id'])
            ->where('is_resmi', true);

        // Filter by siswa
        if (isset($validated['siswa_id'])) {
            $query->where('siswa_id', $validated['siswa_id']);
        }

        // Filter by kelas
        if (isset($validated['kelas_id'])) {
            $query->where('kelas_id', $validated['kelas_id']);
        }

        $sesis = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $sesis,
        ]);
    }

    /**
     * Get statistik ujian
     *
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistikUjian(Ujian $ujian)
    {
        $sesis = SesiUjian::where('ujian_id', $ujian->id)
            ->where('is_resmi', true)
            ->where('status', 'selesai')
            ->get();

        $totalPeserta = $sesis->count();
        $nilaiRataRata = $sesis->avg('nilai_akhir');
        $nilaiTertinggi = $sesis->max('nilai_akhir');
        $nilaiTerendah = $sesis->min('nilai_akhir');
        
        // Hitung distribusi nilai
        $distribusi = [
            'A' => $sesis->where('predikat', 'A')->count(),
            'B' => $sesis->where('predikat', 'B')->count(),
            'C' => $sesis->where('predikat', 'C')->count(),
            'D' => $sesis->where('predikat', 'D')->count(),
            'E' => $sesis->where('predikat', 'E')->count(),
        ];

        // Hitung yang lulus (>= passing grade)
        $lulus = $sesis->where('nilai_akhir', '>=', $ujian->passing_grade)->count();
        $tidakLulus = $totalPeserta - $lulus;

        return response()->json([
            'success' => true,
            'data' => [
                'total_peserta' => $totalPeserta,
                'nilai_rata_rata' => round($nilaiRataRata, 2),
                'nilai_tertinggi' => $nilaiTertinggi,
                'nilai_terendah' => $nilaiTerendah,
                'passing_grade' => $ujian->passing_grade,
                'lulus' => $lulus,
                'tidak_lulus' => $tidakLulus,
                'persentase_lulus' => $totalPeserta > 0 ? round(($lulus / $totalPeserta) * 100, 2) : 0,
                'distribusi_predikat' => $distribusi,
            ],
        ]);
    }

    /**
     * Get daftar periode ujian
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDaftarPeriode(Request $request)
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

        $periodes = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $periodes,
        ]);
    }

    /**
     * Get periode aktif
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPeriodeAktif()
    {
        $periode = PeriodeUjian::with('fases')
            ->where('status', 'aktif')
            ->first();

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

    /**
     * Sync data dari SIAKAD (webhook)
     * 
     * Endpoint untuk menerima notifikasi perubahan data dari SIAKAD
     * seperti perubahan data siswa, kelas, jadwal, dll
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncData(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:siswa,guru,kelas,mapel,jadwal',
            'action' => 'required|in:created,updated,deleted',
            'data' => 'required|array',
        ]);

        // TODO: Implement actual sync logic based on type and action
        // For now, just log the sync request
        
        \Log::info('Data sync request from SIAKAD', [
            'type' => $validated['type'],
            'action' => $validated['action'],
            'data' => $validated['data'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data sync request received',
        ]);
    }

    /**
     * Export hasil ujian untuk SIAKAD
     * 
     * Export hasil ujian dalam format yang bisa diimport ke SIAKAD
     *
     * @param Ujian $ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportHasil(Ujian $ujian)
    {
        $sesis = SesiUjian::where('ujian_id', $ujian->id)
            ->where('is_resmi', true)
            ->where('status', 'selesai')
            ->get();

        $hasil = $sesis->map(function($sesi) {
            return [
                'siswa_id' => $sesi->siswa_id,
                'siswa_nama' => $sesi->siswa_nama,
                'siswa_nis' => $sesi->siswa_nis,
                'kelas_id' => $sesi->kelas_id,
                'nilai' => $sesi->nilai_akhir,
                'predikat' => $sesi->predikat,
                'waktu_mulai' => $sesi->waktu_mulai,
                'waktu_selesai' => $sesi->waktu_selesai,
                'durasi_detik' => $sesi->durasi_detik,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'ujian' => [
                    'id' => $ujian->id,
                    'judul' => $ujian->judul,
                    'mapel_id' => $ujian->mapel_id,
                    'mapel_nama' => $ujian->mapel_nama,
                    'kelas_id' => $ujian->kelas_id,
                    'kelas_nama' => $ujian->kelas_nama,
                    'tipe' => $ujian->tipe,
                ],
                'hasil' => $hasil,
                'exported_at' => now()->toIso8601String(),
            ],
        ]);
    }
}