<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SesiUjian;
use App\Models\Ujian;
use App\Models\ProktorEvent as ProktorEventModel;
use App\Events\ProktorEvent;
use App\Events\SesiUpdated;
use App\Events\AnnouncementEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProktorController extends Controller
{
    public function dashboard(Request $request)
    {
        $access = $this->authorizeProktor($request);
        if ($access) {
            return $access;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_ujian_aktif' => Ujian::where('status', 'published')->count(),
                    'total_siswa_aktif' => SesiUjian::where('status', 'berlangsung')->count(),
                    'total_selesai' => SesiUjian::where('status', 'selesai')->count(),
                    'total_violations' => SesiUjian::sum('tab_switch_count'),
                ],
                'recent_events' => $this->legacyEventsQuery()->limit(10)->get(),
            ],
        ]);
    }

    public function showUjian(Request $request, Ujian $ujian)
    {
        $access = $this->authorizeUjian($request, $ujian);
        if ($access) {
            return $access;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ujian' => $ujian,
                'sessions' => $this->sessionsForUjian($ujian->id)->get()->map(fn ($sesi) => $this->formatSession($sesi)),
                'statistics' => $this->statisticsForUjian($ujian->id),
            ],
        ]);
    }

    public function showSesi(Request $request, SesiUjian $sesi)
    {
        $access = $this->authorizeSesi($request, $sesi);
        if ($access) {
            return $access;
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatSession($sesi),
        ]);
    }

    public function legacyStatistics(Request $request, Ujian $ujian)
    {
        $access = $this->authorizeUjian($request, $ujian);
        if ($access) {
            return $access;
        }

        return response()->json([
            'success' => true,
            'data' => $this->statisticsForUjian($ujian->id),
        ]);
    }

    public function legacyEvents(Request $request, Ujian $ujian)
    {
        $access = $this->authorizeUjian($request, $ujian);
        if ($access) {
            return $access;
        }

        $query = $this->legacyEventsQuery()
            ->where('sesi_ujian.ujian_id', $ujian->id);

        if ($request->filled('type')) {
            $query->where('proktor_events.event_type', $request->type);
        }

        $events = $query
            ->limit((int) $request->input('limit', 50))
            ->get()
            ->map(fn ($event) => [
                'id' => $event->id,
                'event_type' => $event->event_type,
                'description' => $event->description,
                'created_at' => $event->created_at,
                'sesi' => [
                    'id' => $event->sesi_ujian_id,
                    'siswa_nama' => $event->siswa_nama,
                    'siswa_nis' => $event->siswa_nis,
                ],
            ]);

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    public function announceSesi(Request $request, SesiUjian $sesi)
    {
        $access = $this->authorizeSesi($request, $sesi);
        if ($access) {
            return $access;
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        event(new AnnouncementEvent($sesi->ujian_id, $validated['message'], $request->input('_auth_user.nama', 'Admin')));

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil dikirim',
        ]);
    }

    /**
     * Record proktor event dari frontend siswa
     * 
     * Endpoint ini dipanggil oleh frontend saat siswa melakukan
     * aktivitas yang perlu dimonitor (tab switch, copy, paste, dll)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordEvent(Request $request)
    {
        $validated = $request->validate([
            'sesi_ujian_id' => 'required|exists:sesi_ujian,id',
            'event_type' => 'required|string|in:tab_switch,blur,focus,copy,paste,right_click,fullscreen_exit,reconnect,devtools_open',
            'event_data' => 'nullable|array',
        ]);

        try {
            // Get sesi ujian
            $sesi = SesiUjian::with('ujian')->find($validated['sesi_ujian_id']);

            if (!$sesi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi ujian tidak ditemukan',
                ], 404);
            }

            // Verify user owns this sesi
            $authUser = $request->input('_auth_user');
            if ($authUser['user_id'] != $sesi->siswa_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Sesi bukan milik user',
                ], 403);
            }

            // Store event to database
            $proktorEvent = ProktorEventModel::create([
                'sesi_ujian_id' => $sesi->id,
                'tipe' => $validated['event_type'],
                'detail' => $validated['event_data'] ?? [],
            ]);

            // Update tab_switch_count if applicable
            if ($validated['event_type'] === 'tab_switch') {
                $sesi->increment('tab_switch_count');
                $sesi->increment('violation_count');
            }

            // Broadcast event to proktor dashboard via Reverb
            $eventData = array_merge($validated['event_data'] ?? [], [
                'ujian_id' => $sesi->ujian_id,
                'kelas_id' => $sesi->kelas_id,
            ]);

            broadcast(new ProktorEvent(
                $sesi->id,
                $sesi->siswa_id,
                $sesi->siswa_nama,
                $validated['event_type'],
                $eventData
            ))->toOthers();

            Log::info('Proktor event recorded', [
                'sesi_ujian_id' => $sesi->id,
                'siswa_id' => $sesi->siswa_id,
                'event_type' => $validated['event_type'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event recorded',
                'data' => [
                    'event_id' => $proktorEvent->id,
                    'tab_switch_count' => $sesi->tab_switch_count,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to record proktor event', [
                'error' => $e->getMessage(),
                'sesi_ujian_id' => $validated['sesi_ujian_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal merekam event',
            ], 500);
        }
    }

    /**
     * Get proktor events untuk sesi tertentu
     * 
     * Endpoint untuk guru/admin melihat history events
     *
     * @param SesiUjian $sesi
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEvents(SesiUjian $sesi)
    {
        // Verify user has access (guru or admin)
        $authUser = request()->input('_auth_user');
        if (!in_array($authUser['role'], ['guru', 'admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $events = ProktorEventModel::where('sesi_ujian_id', $sesi->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sesi' => [
                    'id' => $sesi->id,
                    'siswa_nama' => $sesi->siswa_nama,
                    'siswa_nis' => $sesi->siswa_nis,
                    'tab_switch_count' => $sesi->tab_switch_count,
                ],
                'events' => $events,
            ],
        ]);
    }

    /**
     * Get statistik proktor untuk ujian tertentu
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistik(Request $request)
    {
        $validated = $request->validate([
            'ujian_id' => 'required|exists:ujian,id',
        ]);

        // Verify user has access
        $authUser = $request->input('_auth_user');
        if (!in_array($authUser['role'], ['guru', 'admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $sesis = SesiUjian::where('ujian_id', $validated['ujian_id'])
            ->where('is_resmi', true)
            ->get();

        // Hitung statistik
        $totalSesi = $sesis->count();
        $totalTabSwitch = $sesis->sum('tab_switch_count');
        $avgTabSwitch = $totalSesi > 0 ? round($totalTabSwitch / $totalSesi, 2) : 0;

        // Hitung distribusi events
        $eventCounts = ProktorEventModel::whereIn('sesi_ujian_id', $sesis->pluck('id'))
            ->selectRaw('tipe, COUNT(*) as count')
            ->groupBy('tipe')
            ->pluck('count', 'tipe')
            ->toArray();

        // Siswa dengan pelanggaran terbanyak
        $topViolators = $sesis->sortByDesc('tab_switch_count')
            ->take(10)
            ->map(function($sesi) {
                return [
                    'siswa_id' => $sesi->siswa_id,
                    'siswa_nama' => $sesi->siswa_nama,
                    'siswa_nis' => $sesi->siswa_nis,
                    'tab_switch_count' => $sesi->tab_switch_count,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'total_sesi' => $totalSesi,
                'total_tab_switch' => $totalTabSwitch,
                'avg_tab_switch' => $avgTabSwitch,
                'event_counts' => $eventCounts,
                'top_violators' => $topViolators,
            ],
        ]);
    }

    /**
     * Get active sessions untuk monitoring realtime
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveSessions(Request $request)
    {
        $validated = $request->validate([
            'ujian_id' => 'required|exists:ujian,id',
        ]);

        // Verify user has access
        $authUser = $request->input('_auth_user');
        if (!in_array($authUser['role'], ['guru', 'admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $activeSessions = SesiUjian::where('ujian_id', $validated['ujian_id'])
            ->whereIn('status', ['berlangsung', 'sedang_mengerjakan'])
            ->where('is_resmi', true)
            ->orderBy('waktu_mulai', 'desc')
            ->get()
            ->map(function($sesi) {
                return [
                    'id' => $sesi->id,
                    'siswa_id' => $sesi->siswa_id,
                    'siswa_nama' => $sesi->siswa_nama,
                    'siswa_nis' => $sesi->siswa_nis,
                    'waktu_mulai' => $sesi->waktu_mulai,
                    'tab_switch_count' => $sesi->tab_switch_count,
                    'durasi_detik' => $sesi->durasi_detik,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $activeSessions,
        ]);
    }

    /**
     * Force finish a session
     *
     * @param SesiUjian $sesi
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceFinish(Request $request, SesiUjian $sesi)
    {
        // Verify user has access
        $authUser = $request->input('_auth_user');
        if (!in_array($authUser['role'], ['guru', 'admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $access = $this->authorizeSesi($request, $sesi);
        if ($access) {
            return $access;
        }

        if (!in_array($sesi->status, ['berlangsung', 'sedang_mengerjakan'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi tidak sedang berlangsung',
            ], 400);
        }

        try {
            $sesi->status = 'selesai';
            $sesi->waktu_selesai = now();
            $sesi->save();

            // Log event
            ProktorEventModel::create([
                'sesi_ujian_id' => $sesi->id,
                'tipe' => 'reconnect',
                'detail' => [
                    'forced_by' => $authUser['user_id'],
                    'forced_by_name' => $authUser['nama'] ?? 'Admin',
                ],
            ]);

            DB::table('proktor_events')->insert([
                'sesi_ujian_id' => $sesi->id,
                'event_type' => 'force_finish',
                'description' => $request->input('reason', 'Sesi dipaksa selesai'),
                'created_at' => now(),
            ]);

            event(new SesiUpdated($sesi));

            Log::info('Session force finished', [
                'sesi_id' => $sesi->id,
                'forced_by' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sesi berhasil diselesaikan',
                'data' => $sesi,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to force finish session', [
                'error' => $e->getMessage(),
                'sesi_id' => $sesi->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan sesi',
            ], 500);
        }
    }

    /**
     * Disqualify a session
     *
     * @param Request $request
     * @param SesiUjian $sesi
     * @return \Illuminate\Http\JsonResponse
     */
    public function disqualify(Request $request, SesiUjian $sesi)
    {
        // Verify user has access
        $authUser = $request->input('_auth_user');
        if (!in_array($authUser['role'], ['guru', 'admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $access = $this->authorizeSesi($request, $sesi);
        if ($access) {
            return $access;
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $sesi->status = 'diskualifikasi';
            $sesi->waktu_selesai = now();
            $sesi->nilai_akhir = 0;
            $sesi->save();

            // Log event
            ProktorEventModel::create([
                'sesi_ujian_id' => $sesi->id,
                'tipe' => 'devtools_open',
                'detail' => [
                    'disqualified_by' => $authUser['user_id'],
                    'disqualified_by_name' => $authUser['nama'] ?? 'Admin',
                    'reason' => $validated['reason'] ?? 'Pelanggaran aturan ujian',
                ],
            ]);

            DB::table('proktor_events')->insert([
                'sesi_ujian_id' => $sesi->id,
                'event_type' => 'disqualify',
                'description' => $validated['reason'],
                'created_at' => now(),
            ]);

            event(new SesiUpdated($sesi));

            Log::info('Session disqualified', [
                'sesi_id' => $sesi->id,
                'disqualified_by' => $authUser['user_id'],
                'reason' => $validated['reason'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil didiskualifikasi',
                'data' => $sesi,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to disqualify session', [
                'error' => $e->getMessage(),
                'sesi_id' => $sesi->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mendiskualifikasi siswa',
            ], 500);
        }
    }

    /**
     * Send announcement to all active sessions in an exam
     *
     * @param Request $request
     * @param int $ujianId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendAnnouncement(Request $request, $ujianId)
    {
        // Verify user has access
        $authUser = $request->input('_auth_user');
        if (!in_array($authUser['role'], ['guru', 'admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        try {
            // Broadcast announcement to all active sessions
            broadcast(new \App\Events\AnnouncementEvent(
                $ujianId,
                $validated['message'],
                $authUser['nama'] ?? 'Admin'
            ));

            Log::info('Announcement sent', [
                'ujian_id' => $ujianId,
                'sent_by' => $authUser['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dikirim',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send announcement', [
                'error' => $e->getMessage(),
                'ujian_id' => $ujianId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pengumuman',
            ], 500);
        }
    }

    /**
     * Get ujian list for monitoring
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUjianList(Request $request)
    {
        // Verify user has access
        $authUser = $request->input('_auth_user');
        if (!in_array($authUser['role'], ['guru', 'admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $ujians = \App\Models\Ujian::with(['periode:id,nama'])
            ->where('status', '!=', 'draft')
            ->when($request->periode_id, function ($query, $periodeId) {
                return $query->where('periode_id', $periodeId);
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    return $query->where('waktu_mulai', '<=', now())
                        ->where('waktu_selesai', '>=', now());
                }
            })
            ->get()
            ->map(function ($ujian) {
                $activeSessions = SesiUjian::where('ujian_id', $ujian->id)
                    ->whereIn('status', ['berlangsung', 'sedang_mengerjakan'])
                    ->where('is_resmi', true)
                    ->count();

                $totalViolations = SesiUjian::where('ujian_id', $ujian->id)
                    ->where('is_resmi', true)
                    ->sum('tab_switch_count');

                return [
                    'id' => $ujian->id,
                    'judul' => $ujian->judul,
                    'mapel_nama' => $ujian->mapel_nama,
                    'kelas_nama' => $ujian->kelas_nama,
                    'periode_nama' => $ujian->periode->nama ?? '-',
                    'waktu_mulai' => $ujian->waktu_mulai,
                    'waktu_selesai' => $ujian->waktu_selesai,
                    'is_active' => now()->between($ujian->waktu_mulai, $ujian->waktu_selesai),
                    'active_sessions' => $activeSessions,
                    'total_violations' => $totalViolations,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $ujians,
        ]);
    }

    /**
     * Get sessions for specific ujian
     *
     * @param Request $request
     * @param int $ujianId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUjianSessions(Request $request, $ujianId)
    {
        // Verify user has access
        $authUser = $request->input('_auth_user');
        if (!in_array($authUser['role'], ['guru', 'admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $sessions = SesiUjian::where('ujian_id', $ujianId)
            ->where('is_resmi', true)
            ->when($request->status, function ($query, $status) {
                $normalized = $this->normalizeStatus($status);

                if ($normalized === 'berlangsung') {
                    return $query->whereIn('status', ['berlangsung', 'sedang_mengerjakan']);
                }

                if ($normalized === 'belum') {
                    return $query->whereIn('status', ['belum', 'belum_mulai']);
                }

                return $query->where('status', $normalized);
            })
            ->orderBy('waktu_mulai', 'desc')
            ->get()
            ->map(function ($sesi) {
                return [
                    'id' => $sesi->id,
                    'siswa_id' => $sesi->siswa_id,
                    'siswa_nama' => $sesi->siswa_nama,
                    'siswa_nis' => $sesi->siswa_nis,
                    'status' => $sesi->status,
                    'waktu_mulai' => $sesi->waktu_mulai,
                    'waktu_selesai' => $sesi->waktu_selesai,
                    'tab_switch_count' => $sesi->tab_switch_count,
                    'last_activity' => $sesi->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    private function authorizeProktor(Request $request)
    {
        $authUser = $request->input('_auth_user');

        if (!in_array($authUser['role'] ?? null, ['guru', 'admin', 'superadmin'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return null;
    }

    private function authorizeUjian(Request $request, Ujian $ujian)
    {
        $access = $this->authorizeProktor($request);
        if ($access) {
            return $access;
        }

        $authUser = $request->input('_auth_user');
        if (($authUser['role'] ?? null) === 'guru' && (int) $ujian->guru_id !== (int) $authUser['user_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return null;
    }

    private function authorizeSesi(Request $request, SesiUjian $sesi)
    {
        $sesi->loadMissing('ujian');
        return $this->authorizeUjian($request, $sesi->ujian);
    }

    private function sessionsForUjian(int $ujianId)
    {
        return SesiUjian::where('ujian_id', $ujianId)->where('is_resmi', true);
    }

    private function statisticsForUjian(int $ujianId): array
    {
        $sessions = $this->sessionsForUjian($ujianId)->get();

        return [
            'total_peserta' => $sessions->count(),
            'sedang_mengerjakan' => $sessions->whereIn('status', ['berlangsung', 'sedang_mengerjakan'])->count(),
            'selesai' => $sessions->where('status', 'selesai')->count(),
            'diskualifikasi' => $sessions->where('status', 'diskualifikasi')->count(),
            'total_violations' => $sessions->sum('tab_switch_count'),
        ];
    }

    private function formatSession(SesiUjian $sesi): array
    {
        $lastActivity = $sesi->last_activity ?? $sesi->updated_at;
        $diffMinutes = $lastActivity ? $lastActivity->diffInMinutes(now()) : 999;

        $connectionStatus = match (true) {
            $diffMinutes < 2 => 'online',
            $diffMinutes <= 5 => 'idle',
            default => 'offline',
        };

        $elapsed = $sesi->waktu_mulai ? $sesi->waktu_mulai->diffInSeconds(now()) : 0;
        $duration = max((int) $sesi->durasi_detik, 1);

        return [
            'id' => $sesi->id,
            'siswa' => [
                'id' => $sesi->siswa_id,
                'nama' => $sesi->siswa_nama,
                'nis' => $sesi->siswa_nis,
            ],
            'status' => $sesi->status,
            'waktu_mulai' => $sesi->waktu_mulai,
            'sisa_waktu' => max($duration - $elapsed, 0),
            'violation_count' => $sesi->violation_count,
            'last_activity' => $lastActivity,
            'connection_status' => $connectionStatus,
            'progress' => min(round(($elapsed / $duration) * 100, 2), 100),
        ];
    }

    private function normalizeStatus(string $status): string
    {
        return match ($status) {
            'sedang_mengerjakan' => 'berlangsung',
            'belum_mulai' => 'belum',
            default => $status,
        };
    }

    private function legacyEventsQuery()
    {
        return DB::table('proktor_events')
            ->leftJoin('sesi_ujian', 'sesi_ujian.id', '=', 'proktor_events.sesi_ujian_id')
            ->select([
                'proktor_events.id',
                'proktor_events.sesi_ujian_id',
                'proktor_events.event_type',
                'proktor_events.description',
                'proktor_events.created_at',
                'sesi_ujian.siswa_nama',
                'sesi_ujian.siswa_nis',
            ])
            ->orderBy('proktor_events.created_at', 'desc');
    }

    /**
     * Get recent events across all exams or specific exam
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentEvents(Request $request)
    {
        // Verify user has access
        $authUser = $request->input('_auth_user');
        if (!in_array($authUser['role'], ['guru', 'admin', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $limit = $request->input('limit', 50);
        $ujianId = $request->input('ujian_id');

        $query = ProktorEventModel::with('sesiUjian:id,siswa_nama,siswa_nis,ujian_id')
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($ujianId) {
            $query->whereHas('sesiUjian', function ($q) use ($ujianId) {
                $q->where('ujian_id', $ujianId);
            });
        }

        $events = $query->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'sesi_ujian_id' => $event->sesi_ujian_id,
                'siswa_nama' => $event->sesiUjian->siswa_nama ?? 'Unknown',
                'siswa_nis' => $event->sesiUjian->siswa_nis ?? '-',
                'tipe' => $event->tipe,
                'detail' => $event->detail,
                'created_at' => $event->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }
}
