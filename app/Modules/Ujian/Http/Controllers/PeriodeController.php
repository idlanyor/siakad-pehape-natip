<?php

namespace App\Modules\Ujian\Http\Controllers;

use App\Models\Semester;
use App\Modules\Ujian\Http\Requests\PeriodeRequest;
use App\Modules\Ujian\Models\FasePeriode;
use App\Modules\Ujian\Models\PeriodeUjian;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PeriodeController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', PeriodeUjian::class);

        $periode = PeriodeUjian::query()
            ->with(['semester.tahunAjaran', 'fases'])
            ->withCount('ujians')
            ->latest()
            ->paginate(15)
            ->through(fn (PeriodeUjian $p) => $this->ringkas($p));

        return Inertia::render('Periode/Index', [
            'periode' => $periode,
            'semester' => $this->opsiSemester(),
            'bolehKelola' => request()->user()->can('create', PeriodeUjian::class),
        ]);
    }

    public function store(PeriodeRequest $request): RedirectResponse
    {
        $this->authorize('create', PeriodeUjian::class);

        $periode = PeriodeUjian::create($request->validated());

        return to_route('ujian.periode.show', $periode)->with('success', 'Periode ujian dibuat. Tambahkan fase-fasenya.');
    }

    public function show(PeriodeUjian $periode): Response
    {
        $this->authorize('view', $periode);
        $periode->load(['semester.tahunAjaran', 'fases.overrideOleh:id,name'])->loadCount('ujians');

        return Inertia::render('Periode/Show', [
            'periode' => [
                ...$this->ringkas($periode),
                'semester_id' => $periode->semester_id,
                'fases' => $periode->fases->map(fn (FasePeriode $f) => [
                    'id' => $f->id,
                    'tipe' => $f->tipe,
                    'tipe_label' => $f->tipe_label,
                    'urutan' => $f->urutan,
                    'tgl_mulai' => $f->tgl_mulai->format('Y-m-d\TH:i'),
                    'tgl_selesai' => $f->tgl_selesai->format('Y-m-d\TH:i'),
                    'aktif' => $f->isAktif(),
                    'status_label' => $f->status_label,
                    'override_status' => $f->override_status,
                    'override_oleh' => $f->overrideOleh?->name,
                    'override_at' => $f->override_at,
                ]),
            ],
            'semester' => $this->opsiSemester(),
            'tipeFase' => collect(FasePeriode::TIPE)->map(fn ($t) => [
                'value' => $t,
                'label' => (new FasePeriode(['tipe' => $t]))->tipe_label,
            ]),
            'bolehKelola' => request()->user()->can('update', $periode),
        ]);
    }

    public function update(PeriodeRequest $request, PeriodeUjian $periode): RedirectResponse
    {
        $this->authorize('update', $periode);
        $periode->update($request->validated());

        return back()->with('success', 'Periode ujian diperbarui.');
    }

    public function destroy(PeriodeUjian $periode): RedirectResponse
    {
        $this->authorize('delete', $periode);

        if ($periode->ujians()->withTrashed()->exists()) {
            return back()->with('error', 'Periode masih memiliki ujian sehingga tidak bisa dihapus.');
        }

        $periode->delete();

        return to_route('ujian.periode.index')->with('success', 'Periode ujian dihapus.');
    }

    private function ringkas(PeriodeUjian $p): array
    {
        return [
            'id' => $p->id,
            'nama' => $p->nama,
            'status' => $p->status,
            'semester' => $this->labelSemester($p->semester),
            'fase_aktif' => $p->faseAktif()?->tipe_label,
            'jumlah_fase' => $p->fases->count(),
            'jumlah_ujian' => (int) $p->ujians_count,
        ];
    }

    private function opsiSemester(): array
    {
        return Semester::with('tahunAjaran')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Semester $s) => ['value' => $s->id, 'label' => $this->labelSemester($s)])
            ->all();
    }

    private function labelSemester(?Semester $s): ?string
    {
        if (! $s) {
            return null;
        }
        $nama = in_array((string) $s->semester, ['1', 'ganjil'], true) ? 'Ganjil' : 'Genap';

        return trim("{$nama} {$s->tahunAjaran?->nama}");
    }
}
