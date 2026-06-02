<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * API Controller untuk integrasi dengan Ujian Service
 * Menyediakan data referensi (kelas, mapel, siswa) dan sync mechanism
 */
class UjianServiceController extends Controller
{
    /**
     * Get list kelas untuk dropdown di ujian-service
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getKelas()
    {
        $kelas = Kelas::select('id', 'nama_kelas', 'jurusan_id', 'tingkat')
            ->with('jurusan:id,nama_jurusan')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get()
            ->map(function ($k) {
                return [
                    'id' => $k->id,
                    'nama' => $k->nama_kelas,
                    'jurusan' => $k->jurusan->nama_jurusan ?? null,
                    'tingkat' => $k->tingkat,
                    'nama_lengkap' => $k->tingkat . ' ' . $k->nama_kelas . ' (' . ($k->jurusan->nama_jurusan ?? '-') . ')'
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $kelas
        ]);
    }

    /**
     * Get list mata pelajaran untuk dropdown di ujian-service
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMapel()
    {
        $mapel = Mapel::select('id', 'nama_mapel', 'kode_mapel', 'kategori')
            ->orderBy('nama_mapel')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'nama' => $m->nama_mapel,
                    'kode' => $m->kode_mapel,
                    'kategori' => $m->kategori
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $mapel
        ]);
    }

    /**
     * Get list siswa by kelas untuk assignment ujian
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSiswaByKelas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kelas_id' => 'required|exists:kelas,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $siswa = Siswa::where('kelas_id', $request->kelas_id)
            ->where('status', 'aktif')
            ->select('id', 'nis', 'nisn', 'nama_lengkap', 'kelas_id', 'user_id')
            ->with('user:id,username,email')
            ->orderBy('nama_lengkap')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'nis' => $s->nis,
                    'nisn' => $s->nisn,
                    'nama' => $s->nama_lengkap,
                    'username' => $s->user->username ?? null,
                    'email' => $s->user->email ?? null,
                    'user_id' => $s->user_id
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $siswa
        ]);
    }

    /**
     * Sync siswa data ke ujian-service
     * Endpoint ini dipanggil oleh ujian-service untuk sync data siswa
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncSiswa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'last_sync' => 'nullable|date',
            'kelas_ids' => 'nullable|array',
            'kelas_ids.*' => 'exists:kelas,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Siswa::with(['user:id,username,email', 'kelas:id,nama_kelas,tingkat']);

        // Filter by last sync time
        if ($request->has('last_sync') && $request->filled('last_sync')) {
            $query->where('updated_at', '>', $request->last_sync);
        }

        // Filter by kelas
        $kelasIds = $request->input('kelas_ids');
        if (!empty($kelasIds) && is_array($kelasIds)) {
            $query->whereIn('kelas_id', $kelasIds);
        }

        $siswa = $query->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'nis' => $s->nis ?? (string) $s->id,
                'nisn' => $s->nisn,
                'nama_lengkap' => $s->nama_lengkap ?? $s->nama,
                'kelas_id' => $s->kelas_id,
                'kelas_nama' => $s->kelas->nama_kelas ?? null,
                'tingkat' => $this->romanToInt($s->kelas->tingkat ?? null),
                'user_id' => $s->user_id,
                'username' => $s->user->username ?? null,
                'email' => $s->user->email ?? null,
                'status' => $s->status ?? 'aktif',
                'updated_at' => $s->updated_at->toIso8601String()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $siswa,
            'count' => $siswa->count(),
            'sync_time' => now()->toIso8601String()
        ]);
    }

    /**
     * Bulk create/update siswa di ujian-service local database
     * Dipanggil dari SIAKAD saat ada perubahan data siswa
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pushSiswaUpdates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'siswa' => 'required|array',
            'siswa.*.id' => 'required|integer',
            'siswa.*.nis' => 'required|string',
            'siswa.*.nama_lengkap' => 'required|string',
            'siswa.*.kelas_id' => 'required|integer',
            'siswa.*.username' => 'required|string',
            'siswa.*.action' => 'required|in:create,update,delete'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // This endpoint is meant to be called BY ujian-service
        // SIAKAD just provides the data, ujian-service will handle the sync
        return response()->json([
            'success' => true,
            'message' => 'Data siswa siap untuk disinkronkan',
            'data' => $request->siswa
        ]);
    }

    /**
     * Verify siswa credentials untuk login di ujian-service
     * Ujian-service akan call endpoint ini untuk validasi login siswa
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifySiswaCredentials(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('username', $request->username)
            ->where('role', 3) // Siswa only
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        $siswa = Siswa::where('user_id', $user->id)
            ->where('status', 'aktif')
            ->with('kelas:id,nama_kelas,tingkat')
            ->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan atau tidak aktif'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'siswa_id' => $siswa->id,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'nama_lengkap' => $siswa->nama_lengkap,
                'kelas_id' => $siswa->kelas_id,
                'kelas_nama' => $siswa->kelas->nama_kelas ?? null,
                'tingkat' => $siswa->kelas->tingkat ?? null,
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email
            ]
        ]);
    }

    /**
     * Get siswa detail by ID
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSiswaDetail($id)
    {
        $siswa = Siswa::with(['user:id,username,email', 'kelas:id,nama_kelas,tingkat'])
            ->find($id);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $siswa->id,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'nama_lengkap' => $siswa->nama_lengkap,
                'kelas_id' => $siswa->kelas_id,
                'kelas_nama' => $siswa->kelas->nama_kelas ?? null,
                'tingkat' => $siswa->kelas->tingkat ?? null,
                'user_id' => $siswa->user_id,
                'username' => $siswa->user->username ?? null,
                'email' => $siswa->user->email ?? null,
                'status' => $siswa->status
            ]
        ]);
    }

    /**
     * Health check endpoint
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    private function romanToInt(?string $roman): ?int
    {
        if ($roman === null) return null;
        $map = ['X' => 10, 'XI' => 11, 'XII' => 12, 'IX' => 9];
        return $map[strtoupper(trim($roman))] ?? (int) $roman;
    }

    public function healthCheck()
    {
        return response()->json([
            'success' => true,
            'message' => 'SIAKAD API is running',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0'
        ]);
    }
}