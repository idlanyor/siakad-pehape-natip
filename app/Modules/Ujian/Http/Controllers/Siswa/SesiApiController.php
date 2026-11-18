<?php

namespace App\Modules\Ujian\Http\Controllers\Siswa;

use App\Modules\Ujian\Http\Controllers\Controller;
use App\Modules\Ujian\Models\ProktorEvent;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Services\SesiUjianService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Endpoint JSON yang dipanggil halaman mengerjakan (session + CSRF).
 *
 * Setiap respons menyertakan status sesi dan sisa waktu, sehingga browser
 * selalu tersinkron dengan server (mis. waktu habis, dipaksa selesai proktor,
 * atau didiskualifikasi).
 */
class SesiApiController extends Controller
{
    public function __construct(private readonly SesiUjianService $sesiService) {}

    public function jawab(Request $request, SesiUjian $sesi): JsonResponse
    {
        $this->authorize('kerjakan', $sesi);
        $this->sesiService->pastikanPerangkat($sesi, $request->header('X-Sesi-Token'));
        $data = $request->validate([
            'soal_id' => ['required', 'integer'],
            'pilihan' => ['nullable', 'string', 'size:1'],
            'teks' => ['nullable', 'string', 'max:20000'],
            'ragu' => ['boolean'],
        ]);

        $soal = SoalUjian::where('ujian_id', $sesi->ujian_id)->findOrFail($data['soal_id']);
        $this->sesiService->simpanJawaban($sesi, $soal, $data['pilihan'] ?? null, $data['teks'] ?? null, $request->boolean('ragu'));

        return $this->status($sesi);
    }

    public function pelanggaran(Request $request, SesiUjian $sesi): JsonResponse
    {
        $this->authorize('kerjakan', $sesi);
        $this->sesiService->pastikanPerangkat($sesi, $request->header('X-Sesi-Token'));
        $data = $request->validate([
            'tipe' => ['required', Rule::in(array_keys(ProktorEvent::PELANGGARAN))],
            'detail' => ['nullable', 'array', 'max:10'],
            'detail.*' => ['nullable', 'string', 'max:255'],
        ]);

        $this->sesiService->catatPelanggaran($sesi, $data['tipe'], $data['detail'] ?? []);

        return $this->status($sesi->refresh());
    }

    /**
     * Heartbeat: memperbarui aktivitas terakhir (status online di monitor
     * proktor) dan memeriksa waktu habis.
     */
    public function ping(Request $request, SesiUjian $sesi): JsonResponse
    {
        $this->authorize('kerjakan', $sesi);
        $this->sesiService->pastikanPerangkat($sesi, $request->header('X-Sesi-Token'));

        if (! $this->sesiService->periksaWaktu($sesi) && $sesi->isBerlangsung()) {
            $sesi->update(['last_activity_at' => now()]);
        }

        // Pengumuman proktor yang belum diterima browser ini.
        $pengumuman = $sesi->ujian->pengumuman()
            ->with('user:id,name')
            ->where('id', '>', $request->integer('setelah'))
            ->get()
            ->map->ringkas();

        return $this->status($sesi, ['pengumuman' => $pengumuman]);
    }

    private function status(SesiUjian $sesi, array $tambahan = []): JsonResponse
    {
        return response()->json([
            'status' => $sesi->status,
            'sisa_detik' => $this->sesiService->sisaDetik($sesi),
            'jumlah_pelanggaran' => $sesi->jumlah_pelanggaran,
            ...$tambahan,
        ]);
    }
}
