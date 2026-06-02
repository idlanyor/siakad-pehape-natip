<?php

namespace App\Http\Controllers;

use App\Models\Ujian;
use App\Models\SesiUjian;
use App\Models\PeriodeUjian;
use App\Models\ProktorEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProktorController extends Controller
{
    /**
     * Display proktor monitoring dashboard
     * 
     * Shows list of all active and upcoming exams with statistics
     */
    public function index(Request $request)
    {
        // Get auth user from JWT middleware
        $authUser = $request->input('_auth_user');
        
        // Check if user has proktor access (guru, admin, superadmin)
        if (!in_array($authUser['role'], ['guru', 'admin', 'superadmin'])) {
            abort(403, 'Unauthorized access to proktor monitoring');
        }

        // Get all active periods
        $periodes = PeriodeUjian::where('status', 'aktif')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'nama', 'tahun_ajaran', 'semester']);

        // Get ujians with statistics
        $ujians = Ujian::with(['periode:id,nama'])
            ->where('status', '!=', 'draft')
            ->when($request->periode_id, function ($query, $periodeId) {
                return $query->where('periode_id', $periodeId);
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    return $query->where('waktu_mulai', '<=', now())
                        ->where('waktu_selesai', '>=', now());
                } elseif ($status === 'upcoming') {
                    return $query->where('waktu_mulai', '>', now());
                } elseif ($status === 'finished') {
                    return $query->where('waktu_selesai', '<', now());
                }
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                      ->orWhere('mapel_nama', 'like', "%{$search}%")
                      ->orWhere('kelas_nama', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(function ($ujian) {
                // Calculate statistics
                $activeSessions = SesiUjian::where('ujian_id', $ujian->id)
                    ->where('status', 'berlangsung')
                    ->where('is_resmi', true)
                    ->count();

                $totalViolations = SesiUjian::where('ujian_id', $ujian->id)
                    ->where('is_resmi', true)
                    ->sum('tab_switch_count');

                $avgTabSwitch = SesiUjian::where('ujian_id', $ujian->id)
                    ->where('is_resmi', true)
                    ->avg('tab_switch_count');

                $isActive = now()->between($ujian->waktu_mulai, $ujian->waktu_selesai);

                return [
                    'id' => $ujian->id,
                    'judul' => $ujian->judul,
                    'mapel_nama' => $ujian->mapel_nama,
                    'kelas_nama' => $ujian->kelas_nama,
                    'periode_nama' => $ujian->periode->nama ?? '-',
                    'waktu_mulai' => $ujian->waktu_mulai,
                    'waktu_selesai' => $ujian->waktu_selesai,
                    'durasi_menit' => $ujian->durasi_menit,
                    'status' => $ujian->status,
                    'status_display' => $this->getStatusDisplay($ujian, $isActive),
                    'is_active' => $isActive,
                    'jumlah_peserta' => $ujian->jumlah_peserta,
                    'active_sessions' => $activeSessions,
                    'total_violations' => $totalViolations,
                    'avg_tab_switch' => round($avgTabSwitch ?? 0, 1),
                ];
            });

        return Inertia::render('Proktor/Index', [
            'periodes' => $periodes,
            'initialUjians' => $ujians,
        ]);
    }

    /**
     * Display detailed monitoring for specific exam
     * 
     * Shows all sessions with realtime updates
     */
    public function show(Request $request, $id)
    {
        // Get auth user from JWT middleware
        $authUser = $request->input('_auth_user');
        
        // Check if user has proktor access
        if (!in_array($authUser['role'], ['guru', 'admin', 'superadmin'])) {
            abort(403, 'Unauthorized access to proktor monitoring');
        }

        // Get ujian with details
        $ujian = Ujian::with(['periode:id,nama'])
            ->findOrFail($id);

        // Get all sessions for this ujian
        $sessions = SesiUjian::where('ujian_id', $id)
            ->where('is_resmi', true)
            ->orderBy('waktu_mulai', 'desc')
            ->get()
            ->map(function ($sesi) use ($ujian) {
                // Calculate progress
                $totalSoal = $ujian->soal()->count();
                $jawaban = $sesi->jawaban()->count();
                $progress = $totalSoal > 0 ? round(($jawaban / $totalSoal) * 100) : 0;

                // Calculate remaining time
                $sisaWaktu = 0;
                if ($sesi->status === 'berlangsung' && $sesi->waktu_selesai) {
                    $sisaWaktu = max(0, now()->diffInSeconds($sesi->waktu_selesai, false));
                }

                return [
                    'id' => $sesi->id,
                    'siswa_id' => $sesi->siswa_id,
                    'siswa_nama' => $sesi->siswa_nama,
                    'siswa_nis' => $sesi->siswa_nis,
                    'kelas_id' => $sesi->kelas_id,
                    'status' => $sesi->status,
                    'waktu_mulai' => $sesi->waktu_mulai,
                    'waktu_selesai' => $sesi->waktu_selesai,
                    'durasi_detik' => $sesi->durasi_detik,
                    'tab_switch_count' => $sesi->tab_switch_count,
                    'progress' => $progress,
                    'jawaban_count' => $jawaban,
                    'total_soal' => $totalSoal,
                    'sisa_waktu' => $sisaWaktu,
                    'last_activity' => $sesi->updated_at,
                ];
            });

        return Inertia::render('Proktor/Show', [
            'ujian' => [
                'id' => $ujian->id,
                'judul' => $ujian->judul,
                'deskripsi' => $ujian->deskripsi,
                'mapel_nama' => $ujian->mapel_nama,
                'kelas_nama' => $ujian->kelas_nama,
                'guru_nama' => $ujian->guru_nama,
                'waktu_mulai' => $ujian->waktu_mulai,
                'waktu_selesai' => $ujian->waktu_selesai,
                'durasi_menit' => $ujian->durasi_menit,
                'status' => $ujian->status,
            ],
            'sessions' => $sessions,
        ]);
    }

    /**
     * Get status display text
     */
    private function getStatusDisplay($ujian, $isActive)
    {
        if ($isActive) {
            return 'Sedang Berlangsung';
        }

        if (now()->lt($ujian->waktu_mulai)) {
            return 'Akan Datang';
        }

        if (now()->gt($ujian->waktu_selesai)) {
            return 'Selesai';
        }

        return ucfirst($ujian->status);
    }
}
