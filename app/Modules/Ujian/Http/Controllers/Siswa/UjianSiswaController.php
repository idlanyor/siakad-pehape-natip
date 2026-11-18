<?php

namespace App\Modules\Ujian\Http\Controllers\Siswa;

use App\Models\Siswa;
use App\Modules\Ujian\Http\Controllers\Controller;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\PenyajiSesi;
use App\Modules\Ujian\Services\SesiUjianService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman siswa: daftar ujian, mengerjakan, dan hasil.
 */
class UjianSiswaController extends Controller
{
    public function __construct(
        private readonly SesiUjianService $sesiService,
        private readonly PenyajiSesi $penyaji,
    ) {}

    public function index(Request $request): Response
    {
        $siswa = $this->siswa($request);

        $sesiSaya = SesiUjian::where('siswa_id', $siswa->id)->get()->keyBy('ujian_id');

        // Ujian kelas siswa yang terbit/selesai, ditambah ujian lama yang pernah dikerjakan (mis. sebelum pindah kelas).
        $ujian = Ujian::query()
            ->where(fn ($q) => $q
                ->where(fn ($k) => $k->where('kelas_id', $siswa->kelas_id)->whereIn('status', [Ujian::STATUS_PUBLISHED, Ujian::STATUS_SELESAI]))
                ->orWhereIn('id', $sesiSaya->keys()))
            ->with('mapel:id,nama_mapel')
            ->withCount('soal')
            ->orderByDesc('waktu_mulai')
            ->get()
            ->map(function (Ujian $u) use ($sesiSaya) {
                /** @var SesiUjian|null $sesi */
                $sesi = $sesiSaya->get($u->id);
                if ($sesi?->isBerlangsung()) {
                    $sesi->setRelation('ujian', $u);
                    $this->sesiService->periksaWaktu($sesi);
                }

                return [
                    'id' => $u->id,
                    'judul' => $u->judul,
                    'deskripsi' => $u->deskripsi,
                    'mapel' => $u->mapel?->nama_mapel,
                    'tipe' => $u->tipe,
                    'waktu_mulai' => $u->waktu_mulai,
                    'waktu_selesai' => $u->waktu_selesai,
                    'durasi_menit' => $u->durasi_menit,
                    'jumlah_soal' => (int) $u->soal_count,
                    'jendela' => match (true) {
                        now()->lt($u->waktu_mulai) => 'belum_dibuka',
                        $u->isDibuka() => 'dibuka',
                        default => 'ditutup',
                    },
                    'sesi' => $sesi ? ['id' => $sesi->id, 'status' => $sesi->status] : null,
                ];
            });

        return Inertia::render('Siswa/Daftar', ['ujian' => $ujian]);
    }

    public function mulai(Request $request, Ujian $ujian): RedirectResponse
    {
        $this->authorize('kerjakan', $ujian);

        $sesi = $this->sesiService->mulai($ujian, $this->siswa($request));

        return to_route('ujian.siswa.kerjakan', $sesi);
    }

    public function kerjakan(SesiUjian $sesi): Response|RedirectResponse
    {
        $this->authorize('kerjakan', $sesi);

        if ($this->sesiService->periksaWaktu($sesi) || ! $sesi->isBerlangsung()) {
            return to_route('ujian.siswa.hasil', $sesi);
        }

        return Inertia::render('Siswa/Kerjakan', $this->penyaji->pengerjaan($sesi));
    }

    public function selesai(SesiUjian $sesi): RedirectResponse
    {
        $this->authorize('kerjakan', $sesi);

        if ($sesi->isBerlangsung()) {
            $this->sesiService->selesaikan($sesi);
        }

        return to_route('ujian.siswa.hasil', $sesi)->with('success', 'Jawaban Anda sudah dikumpulkan.');
    }

    public function hasil(SesiUjian $sesi): Response|RedirectResponse
    {
        $this->authorize('kerjakan', $sesi);

        if (! $this->sesiService->periksaWaktu($sesi)) {
            return to_route('ujian.siswa.kerjakan', $sesi);
        }

        return Inertia::render('Siswa/Hasil', $this->penyaji->hasil($sesi));
    }

    private function siswa(Request $request): Siswa
    {
        $user = $request->user();
        abort_unless($user->isSiswa() && $user->siswa, 403, 'Halaman ini khusus siswa.');

        return $user->siswa;
    }
}
