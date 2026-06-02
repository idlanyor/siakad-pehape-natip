<?php

namespace App\Http\Controllers;

use App\Models\Ujian;
use App\Models\PeriodeUjian;
use App\Models\SesiUjian;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display dashboard based on user role
     */
    public function index(Request $request): Response
    {
        $user = $request->input('_auth_user', []);
        $role = $user['role'] ?? 'siswa';

        // Get statistics based on role
        $stats = $this->getStatistics($user, $role);
        
        // Get recent/available ujian
        $ujianData = $this->getUjianData($user, $role);
        
        // Get active periode
        $activePeriode = $this->getActivePeriode();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentUjian' => $ujianData['recent'] ?? [],
            'availableUjian' => $ujianData['available'] ?? [],
            'activePeriode' => $activePeriode,
        ]);
    }

    /**
     * Get statistics based on user role
     */
    private function getStatistics(array $user, string $role): array
    {
        $stats = [
            'total_ujian' => 0,
            'ujian_aktif' => 0,
            'periode_aktif' => 0,
        ];

        if ($role === 'siswa') {
            // Siswa statistics
            $kelasId = $user['kelas_id'] ?? null;
            
            $stats['ujian_tersedia'] = Ujian::where('kelas_id', $kelasId)
                ->where('is_published', true)
                ->where('waktu_mulai', '<=', now())
                ->where('waktu_selesai', '>=', now())
                ->count();
                
            $stats['ujian_selesai'] = SesiUjian::where('siswa_id', $user['user_id'])
                ->where('status', 'selesai')
                ->count();
                
        } else {
            // Guru/Admin statistics
            $query = Ujian::query();
            
            if ($role === 'guru') {
                $query->where('guru_id', $user['user_id']);
            }
            
            $stats['total_ujian'] = $query->count();
            $stats['ujian_aktif'] = (clone $query)
                ->where('is_published', true)
                ->where('waktu_mulai', '<=', now())
                ->where('waktu_selesai', '>=', now())
                ->count();
                
            // Total siswa yang pernah mengikuti ujian
            $stats['total_siswa'] = SesiUjian::whereIn('ujian_id', $query->pluck('id'))
                ->distinct('siswa_id')
                ->count('siswa_id');
        }

        $stats['periode_aktif'] = PeriodeUjian::where('is_active', true)->count();

        return $stats;
    }

    /**
     * Get ujian data based on user role
     */
    private function getUjianData(array $user, string $role): array
    {
        $data = [];

        if ($role === 'siswa') {
            // Get available ujian for siswa
            $kelasId = $user['kelas_id'] ?? null;
            
            $data['available'] = Ujian::where('kelas_id', $kelasId)
                ->where('is_published', true)
                ->where('waktu_mulai', '<=', now())
                ->where('waktu_selesai', '>=', now())
                ->orderBy('waktu_mulai', 'asc')
                ->limit(5)
                ->get()
                ->map(function ($ujian) {
                    return [
                        'id' => $ujian->id,
                        'judul' => $ujian->judul,
                        'waktu_mulai' => $ujian->waktu_mulai,
                        'waktu_selesai' => $ujian->waktu_selesai,
                        'durasi_menit' => $ujian->durasi_menit,
                    ];
                });
        } else {
            // Get recent ujian for guru/admin
            $query = Ujian::query();
            
            if ($role === 'guru') {
                $query->where('guru_id', $user['user_id']);
            }
            
            $data['recent'] = $query->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($ujian) {
                    return [
                        'id' => $ujian->id,
                        'judul' => $ujian->judul,
                        'kelas_nama' => 'Kelas ' . $ujian->kelas_id, // TODO: Get from SIAKAD
                        'waktu_mulai' => $ujian->waktu_mulai,
                        'durasi_menit' => $ujian->durasi_menit,
                        'is_published' => $ujian->is_published,
                    ];
                });
        }

        return $data;
    }

    /**
     * Get active periode with current fase
     */
    private function getActivePeriode(): ?array
    {
        $periode = PeriodeUjian::where('is_active', true)
            ->with('fases')
            ->first();

        if (!$periode) {
            return null;
        }

        // Get current active fase
        $activeFase = $periode->fases()
            ->where('is_active', true)
            ->first();

        return [
            'id' => $periode->id,
            'nama' => $periode->nama,
            'tahun_ajaran' => $periode->tahun_ajaran,
            'semester' => $periode->semester,
            'tanggal_mulai' => $periode->tanggal_mulai,
            'tanggal_selesai' => $periode->tanggal_selesai,
            'fase_aktif' => $activeFase ? $activeFase->nama : null,
        ];
    }
}
