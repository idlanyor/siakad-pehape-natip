<?php

namespace App\Modules\Ujian\Http\Controllers;

use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\ProktorService;
use App\Modules\Ujian\Services\SesiUjianService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Pengawasan ujian oleh guru pemilik, guru pengawas, atau admin.
 */
class ProktorController extends Controller
{
    public function __construct(
        private readonly ProktorService $proktor,
        private readonly SesiUjianService $sesiService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $user->isGuru(), 403);

        $ujian = $this->queryDiawasi($request)
            ->where('status', '!=', Ujian::STATUS_DRAFT)
            ->where('waktu_selesai', '>=', now()->subDays(7))
            ->with(['mapel:id,nama_mapel', 'kelas:id,nama_kelas'])
            ->withCount(['sesi as berlangsung_count' => fn ($q) => $q->where('status', SesiUjian::STATUS_BERLANGSUNG)])
            ->orderBy('waktu_mulai')
            ->get()
            ->map(fn (Ujian $u) => [
                'id' => $u->id,
                'judul' => $u->judul,
                'mapel' => $u->mapel?->nama_mapel,
                'kelas' => $u->kelas?->nama_kelas,
                'waktu_mulai' => $u->waktu_mulai,
                'waktu_selesai' => $u->waktu_selesai,
                'dibuka' => $u->isDibuka(),
                'berlangsung' => (int) $u->berlangsung_count,
            ]);

        return Inertia::render('Proktor/Index', ['ujian' => $ujian]);
    }

    public function monitor(Ujian $ujian): Response
    {
        $this->authorize('awasi', $ujian);
        $ujian->load(['mapel:id,nama_mapel', 'kelas:id,nama_kelas']);

        return Inertia::render('Proktor/Monitor', [
            'ujian' => [
                'id' => $ujian->id,
                'judul' => $ujian->judul,
                'mapel' => $ujian->mapel?->nama_mapel,
                'kelas' => $ujian->kelas?->nama_kelas,
                'waktu_mulai' => $ujian->waktu_mulai,
                'waktu_selesai' => $ujian->waktu_selesai,
                'dibuka' => $ujian->isDibuka(),
            ],
            // Dimuat ulang oleh polling (router.reload only).
            'peserta' => fn () => $this->proktor->peserta($ujian),
            'statistik' => fn () => $this->proktor->statistik($ujian),
            'event' => fn () => $this->proktor->eventTerbaru($ujian, 30),
            'pengumuman' => fn () => $ujian->pengumuman()->with('user:id,name')->latest('id')->limit(10)->get()->map->ringkas(),
            'batasPelanggaran' => (int) config('ujian.batas_pelanggaran'),
            'diperbarui' => fn () => now()->toIso8601String(),
        ]);
    }

    public function paksaSelesai(Request $request, SesiUjian $sesi): RedirectResponse
    {
        $this->authorize('awasi', $sesi);
        $alasan = $request->validate(['alasan' => ['nullable', 'string', 'max:500']])['alasan'] ?? null;

        $this->sesiService->paksaSelesai($sesi, $request->user(), $alasan);

        return back()->with('success', "Sesi {$sesi->siswa->nama} diselesaikan dan dinilai.");
    }

    public function diskualifikasi(Request $request, SesiUjian $sesi): RedirectResponse
    {
        $this->authorize('awasi', $sesi);
        $alasan = $request->validate(['alasan' => ['required', 'string', 'max:500']])['alasan'];

        $this->sesiService->diskualifikasi($sesi, $request->user(), $alasan);

        return back()->with('success', "{$sesi->siswa->nama} didiskualifikasi.");
    }

    public function reset(Request $request, SesiUjian $sesi): RedirectResponse
    {
        $this->authorize('awasi', $sesi);
        $alasan = $request->validate(['alasan' => ['nullable', 'string', 'max:500']])['alasan'] ?? null;

        $this->sesiService->reset($sesi, $request->user(), $alasan);

        return back()->with('success', "Sesi {$sesi->siswa->nama} direset; siswa bisa mulai ulang.");
    }

    public function umumkan(Request $request, Ujian $ujian): RedirectResponse
    {
        $this->authorize('awasi', $ujian);
        $pesan = $request->validate(['pesan' => ['required', 'string', 'max:500']])['pesan'];

        $ujian->pengumuman()->create(['user_id' => $request->user()->id, 'pesan' => $pesan]);

        return back()->with('success', 'Pengumuman dikirim; peserta menerimanya dalam beberapa detik.');
    }

    public function tambahWaktu(Request $request, SesiUjian $sesi): RedirectResponse
    {
        $this->authorize('awasi', $sesi);
        $data = $this->validasiTambahWaktu($request);

        $this->sesiService->tambahWaktu($sesi, $data['menit'], $request->user(), $data['alasan'] ?? null);

        return back()->with('success', "Waktu {$sesi->siswa->nama} ditambah {$data['menit']} menit.");
    }

    /** Tambah waktu semua sesi yang sedang berlangsung, sekaligus mengumumkannya. */
    public function tambahWaktuSemua(Request $request, Ujian $ujian): RedirectResponse
    {
        $this->authorize('awasi', $ujian);
        $data = $this->validasiTambahWaktu($request);

        $sesi = $ujian->sesi()->where('status', SesiUjian::STATUS_BERLANGSUNG)->get();
        foreach ($sesi as $s) {
            $s->setRelation('ujian', $ujian);
            $this->sesiService->tambahWaktu($s, $data['menit'], $request->user(), $data['alasan'] ?? null);
        }

        if ($sesi->isNotEmpty()) {
            $ujian->pengumuman()->create([
                'user_id' => $request->user()->id,
                'pesan' => "Waktu ujian ditambah {$data['menit']} menit.".(! empty($data['alasan']) ? " {$data['alasan']}" : ''),
            ]);
        }

        return back()->with('success', "Waktu {$sesi->count()} peserta yang sedang mengerjakan ditambah {$data['menit']} menit.");
    }

    private function validasiTambahWaktu(Request $request): array
    {
        return $request->validate([
            'menit' => ['required', 'integer', 'min:1', 'max:180'],
            'alasan' => ['nullable', 'string', 'max:500'],
        ]);
    }

    /** Admin: semua ujian. Guru: ujian miliknya atau yang ia awasi. */
    private function queryDiawasi(Request $request): Builder
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return Ujian::query();
        }

        $guruId = $user->guru?->id ?? 0;

        return Ujian::query()->where(fn ($q) => $q
            ->where('guru_id', $guruId)
            ->orWhereHas('pengawas', fn ($p) => $p->whereKey($guruId)));
    }
}
