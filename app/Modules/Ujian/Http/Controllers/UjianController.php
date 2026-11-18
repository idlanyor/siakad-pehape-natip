<?php

namespace App\Modules\Ujian\Http\Controllers;

use App\Modules\Ujian\Http\Requests\UjianRequest;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\PengelolaanUjianService;
use App\Modules\Ujian\Support\OpsiForm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UjianController extends Controller
{
    public function __construct(private readonly PengelolaanUjianService $pengelolaan) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $user->isGuru(), 403);
        $filter = $request->only(['periode_id', 'status', 'cari']);

        $ujian = Ujian::query()
            ->when(! $user->isAdmin(), fn ($q) => $q->where('guru_id', $user->guru?->id ?? 0))
            ->with(['periode:id,nama', 'mapel:id,nama_mapel', 'kelas:id,nama_kelas', 'guru:id,nama'])
            ->withCount(['soal', 'sesi'])
            ->when($filter['periode_id'] ?? null, fn ($q, $v) => $q->where('periode_id', $v))
            ->when($filter['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filter['cari'] ?? null, fn ($q, $v) => $q->where('judul', 'like', "%{$v}%"))
            ->latest('waktu_mulai')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Ujian $u) => $this->ringkas($u));

        return Inertia::render('Ujian/Index', [
            'ujian' => $ujian,
            'filter' => $filter,
            'periode' => OpsiForm::periodeTerbuka(),
            'bolehBuat' => $user->can('create', Ujian::class),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Ujian::class);

        return Inertia::render('Ujian/Form', [...$this->opsi($request), 'ujian' => null]);
    }

    public function store(UjianRequest $request): RedirectResponse
    {
        $this->authorize('create', Ujian::class);

        $data = $request->atribut();
        if ($request->user()->isGuru()) {
            $data['guru_id'] = $request->user()->guru->id;
        }
        $ujian = Ujian::create([...$data, 'status' => Ujian::STATUS_DRAFT]);

        return to_route('ujian.soal.index', $ujian)->with('success', 'Ujian dibuat. Sekarang susun soalnya.');
    }

    public function show(Request $request, Ujian $ujian): Response
    {
        $this->authorize('view', $ujian);
        $ujian->load(['periode:id,nama', 'mapel:id,nama_mapel', 'kelas:id,nama_kelas', 'guru:id,nama', 'pengawas:id,nama'])
            ->loadCount(['soal', 'sesi']);
        $user = $request->user();

        return Inertia::render('Ujian/Show', [
            'ujian' => [
                ...$this->ringkas($ujian),
                'deskripsi' => $ujian->deskripsi,
                'durasi_menit' => $ujian->durasi_menit,
                'passing_grade' => (float) $ujian->passing_grade,
                'acak_soal' => $ujian->acak_soal,
                'acak_opsi' => $ujian->acak_opsi,
                'tampilkan_nilai' => $ujian->tampilkan_nilai,
                'pengawas' => $ujian->pengawas->map(fn ($g) => ['id' => $g->id, 'nama' => $g->nama]),
            ],
            'izin' => [
                'ubah' => $user->can('update', $ujian),
                'hapus' => $user->can('delete', $ujian),
                'nilai' => $user->can('nilai', $ujian),
                'awasi' => $user->can('awasi', $ujian),
                'tugaskanPengawas' => $user->can('tugaskanPengawas', $ujian),
            ],
            'guru' => $user->can('tugaskanPengawas', $ujian) ? OpsiForm::guru() : [],
        ]);
    }

    public function edit(Request $request, Ujian $ujian): Response
    {
        $this->authorize('update', $ujian);

        return Inertia::render('Ujian/Form', [
            ...$this->opsi($request),
            'ujian' => [
                ...$ujian->only(['id', 'periode_id', 'judul', 'deskripsi', 'tipe', 'mapel_id', 'kelas_id', 'guru_id',
                    'durasi_menit', 'acak_soal', 'acak_opsi', 'tampilkan_nilai']),
                'passing_grade' => (float) $ujian->passing_grade,
                'waktu_mulai' => $ujian->waktu_mulai->format('Y-m-d\TH:i'),
                'waktu_selesai' => $ujian->waktu_selesai->format('Y-m-d\TH:i'),
            ],
        ]);
    }

    public function update(UjianRequest $request, Ujian $ujian): RedirectResponse
    {
        $this->authorize('update', $ujian);
        $ujian->update($request->atribut());

        return to_route('ujian.ujian.show', $ujian)->with('success', 'Ujian diperbarui.');
    }

    public function destroy(Ujian $ujian): RedirectResponse
    {
        $this->authorize('delete', $ujian);
        $ujian->delete();

        return to_route('ujian.ujian.index')->with('success', 'Ujian dihapus.');
    }

    public function status(Request $request, Ujian $ujian): RedirectResponse
    {
        $this->authorize('update', $ujian);
        $status = $request->validate(['status' => ['required', Rule::in([
            Ujian::STATUS_DRAFT, Ujian::STATUS_PUBLISHED, Ujian::STATUS_SELESAI,
        ])]])['status'];

        match ($status) {
            Ujian::STATUS_PUBLISHED => $this->pengelolaan->publikasikan($ujian),
            Ujian::STATUS_DRAFT => $this->pengelolaan->kembalikanKeDraft($ujian),
            Ujian::STATUS_SELESAI => $this->pengelolaan->tandaiSelesai($ujian),
        };

        return back()->with('success', "Status ujian: {$ujian->status_label}.");
    }

    public function pengawas(Request $request, Ujian $ujian): RedirectResponse
    {
        $this->authorize('tugaskanPengawas', $ujian);
        $ids = $request->validate([
            'guru_ids' => ['present', 'array'],
            'guru_ids.*' => ['integer', Rule::exists('gurus', 'id')],
        ])['guru_ids'];

        $ujian->pengawas()->sync($ids);

        return back()->with('success', 'Pengawas ujian diperbarui.');
    }

    private function ringkas(Ujian $u): array
    {
        return [
            'id' => $u->id,
            'judul' => $u->judul,
            'tipe' => $u->tipe,
            'status' => $u->status,
            'status_label' => $u->status_label,
            'status_badge' => $u->status_badge,
            'periode' => $u->periode?->nama,
            'mapel' => $u->mapel?->nama_mapel,
            'kelas' => $u->kelas?->nama_kelas,
            'guru' => $u->guru?->nama,
            'waktu_mulai' => $u->waktu_mulai,
            'waktu_selesai' => $u->waktu_selesai,
            'jumlah_soal' => (int) $u->soal_count,
            'jumlah_sesi' => (int) $u->sesi_count,
        ];
    }

    private function opsi(Request $request): array
    {
        $user = $request->user();

        return [
            'periode' => OpsiForm::periodeTerbuka(),
            'mapel' => OpsiForm::mapel($user),
            'kelas' => OpsiForm::kelas(),
            'guru' => $user->isAdmin() ? OpsiForm::guru() : [],
            'tipe' => Ujian::TIPE,
        ];
    }
}
