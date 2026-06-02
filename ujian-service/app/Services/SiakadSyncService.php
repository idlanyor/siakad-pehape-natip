<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\UserSiswa;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk sinkronisasi data dari SIAKAD utama
 * Siswa login menggunakan credentials yang disinkronkan dari SIAKAD
 */
class SiakadSyncService
{
    protected $siakadBaseUrl;
    protected $serviceKey;

    public function __construct()
    {
        $this->siakadBaseUrl = config('services.siakad.base_url');
        $this->serviceKey = config('services.siakad.service_key');
    }

    /**
     * Sync semua siswa dari SIAKAD
     * 
     * @param array $kelasIds Optional filter by kelas IDs
     * @return array
     */
    public function syncAllSiswa(array $kelasIds = [])
    {
        try {
            $lastSync = Siswa::max('synced_at');

            $response = Http::timeout(30)
                ->post("{$this->siakadBaseUrl}/api/ujian-service/sync/siswa", [
                    'last_sync' => $lastSync,
                    'kelas_ids' => $kelasIds
                ]);

            if (!$response->successful()) {
                Log::error('SIAKAD sync failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'message' => 'Gagal mengambil data dari SIAKAD',
                    'error' => $response->body()
                ];
            }

            $data = $response->json();
            $siswaData = $data['data'] ?? [];
            $syncTime = $data['sync_time'] ?? now()->toIso8601String();

            $created = 0;
            $updated = 0;
            $errors = [];

            DB::beginTransaction();
            try {
                foreach ($siswaData as $siswa) {
                    $existing = Siswa::where('siakad_id', $siswa['id'])->first();

                    $siswaRecord = [
                        'siakad_id' => $siswa['id'],
                        'nis' => $siswa['nis'],
                        'nisn' => $siswa['nisn'],
                        'nama_lengkap' => $siswa['nama_lengkap'],
                        'kelas_id' => $siswa['kelas_id'],
                        'kelas_nama' => $siswa['kelas_nama'],
                        'tingkat' => $siswa['tingkat'],
                        'username' => $siswa['username'],
                        'email' => $siswa['email'],
                        'status' => $siswa['status'],
                        'synced_at' => $syncTime
                    ];

                    if ($existing) {
                        $existing->update($siswaRecord);
                        $updated++;
                        
                        // Update or create user_siswa
                        UserSiswa::createOrUpdateFromSiswa($existing);
                    } else {
                        $newSiswa = Siswa::create($siswaRecord);
                        $created++;
                        
                        // Create user_siswa for new siswa
                        UserSiswa::createOrUpdateFromSiswa($newSiswa);
                    }
                }

                DB::commit();

                Log::info('SIAKAD sync completed', [
                    'created' => $created,
                    'updated' => $updated,
                    'total' => count($siswaData)
                ]);

                return [
                    'success' => true,
                    'message' => 'Sinkronisasi berhasil',
                    'created' => $created,
                    'updated' => $updated,
                    'total' => count($siswaData),
                    'sync_time' => $syncTime
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('SIAKAD sync database error', [
                    'error' => $e->getMessage()
                ]);

                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan data ke database',
                    'error' => $e->getMessage()
                ];
            }
        } catch (\Exception $e) {
            Log::error('SIAKAD sync exception', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify siswa credentials against SIAKAD
     * Used during login to validate credentials
     * 
     * @param string $username
     * @param string $password
     * @return array|null
     */
    public function verifySiswaCredentials(string $username, string $password)
    {
        try {
            $response = Http::timeout(10)
                ->post("{$this->siakadBaseUrl}/api/ujian-service/verify-credentials", [
                    'username' => $username,
                    'password' => $password
                ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();
            
            if (!$data['success']) {
                return null;
            }

            return $data['data'];
        } catch (\Exception $e) {
            Log::error('SIAKAD credential verification failed', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Get or create local siswa record from SIAKAD data
     * 
     * @param array $siakadData
     * @return Siswa
     */
    public function getOrCreateLocalSiswa(array $siakadData)
    {
        $siswa = Siswa::where('siakad_id', $siakadData['siswa_id'])->first();

        if (!$siswa) {
            $siswa = Siswa::create([
                'siakad_id' => $siakadData['siswa_id'],
                'nis' => $siakadData['nis'],
                'nisn' => $siakadData['nisn'],
                'nama_lengkap' => $siakadData['nama_lengkap'],
                'kelas_id' => $siakadData['kelas_id'],
                'kelas_nama' => $siakadData['kelas_nama'],
                'tingkat' => $siakadData['tingkat'],
                'username' => $siakadData['username'],
                'email' => $siakadData['email'],
                'status' => 'aktif',
                'synced_at' => now()
            ]);
        } else {
            // Update if data changed
            $siswa->update([
                'nis' => $siakadData['nis'],
                'nisn' => $siakadData['nisn'],
                'nama_lengkap' => $siakadData['nama_lengkap'],
                'kelas_id' => $siakadData['kelas_id'],
                'kelas_nama' => $siakadData['kelas_nama'],
                'tingkat' => $siakadData['tingkat'],
                'username' => $siakadData['username'],
                'email' => $siakadData['email'],
                'synced_at' => now()
            ]);
        }

        return $siswa;
    }

    /**
     * Get kelas list from SIAKAD
     * 
     * @param string $token Admin JWT token
     * @return array
     */
    public function getKelasList(string $token)
    {
        try {
            $response = Http::timeout(10)
                ->withToken($token)
                ->get("{$this->siakadBaseUrl}/api/ujian-service/kelas");

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            return $data['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to get kelas from SIAKAD', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Get mapel list from SIAKAD
     * 
     * @param string $token Admin JWT token
     * @return array
     */
    public function getMapelList(string $token)
    {
        try {
            $response = Http::timeout(10)
                ->withToken($token)
                ->get("{$this->siakadBaseUrl}/api/ujian-service/mapel");

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            return $data['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to get mapel from SIAKAD', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Get siswa by kelas from SIAKAD
     * 
     * @param string $token Admin JWT token
     * @param int $kelasId
     * @return array
     */
    public function getSiswaByKelas(string $token, int $kelasId)
    {
        try {
            $response = Http::timeout(10)
                ->withToken($token)
                ->get("{$this->siakadBaseUrl}/api/ujian-service/siswa/by-kelas", [
                    'kelas_id' => $kelasId
                ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            return $data['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to get siswa by kelas from SIAKAD', [
                'kelas_id' => $kelasId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Health check SIAKAD connection
     * 
     * @return bool
     */
    public function healthCheck()
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->siakadBaseUrl}/api/ujian-service/health");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
