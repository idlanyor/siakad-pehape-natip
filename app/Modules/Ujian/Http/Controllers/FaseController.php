<?php

namespace App\Modules\Ujian\Http\Controllers;

use App\Modules\Ujian\Http\Requests\FaseRequest;
use App\Modules\Ujian\Models\FasePeriode;
use App\Modules\Ujian\Models\PeriodeUjian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FaseController extends Controller
{
    public function store(FaseRequest $request, PeriodeUjian $periode): RedirectResponse
    {
        $this->authorize('kelolaFase', PeriodeUjian::class);
        $periode->fases()->create($request->validated());

        return back()->with('success', 'Fase ditambahkan.');
    }

    public function update(FaseRequest $request, FasePeriode $fase): RedirectResponse
    {
        $this->authorize('kelolaFase', PeriodeUjian::class);
        $fase->update($request->validated());

        return back()->with('success', 'Fase diperbarui.');
    }

    public function destroy(FasePeriode $fase): RedirectResponse
    {
        $this->authorize('kelolaFase', PeriodeUjian::class);
        $fase->delete();

        return back()->with('success', 'Fase dihapus.');
    }

    public function override(Request $request, FasePeriode $fase): RedirectResponse
    {
        $this->authorize('kelolaFase', PeriodeUjian::class);
        $aksi = $request->validate(['aksi' => ['required', Rule::in(['buka', 'tutup', 'reset'])]])['aksi'];

        match ($aksi) {
            'buka' => $fase->paksaBuka($request->user()),
            'tutup' => $fase->paksaTutup($request->user()),
            'reset' => $fase->resetOverride(),
        };

        $pesan = ['buka' => 'dipaksa buka', 'tutup' => 'dipaksa tutup', 'reset' => 'kembali mengikuti jadwal'][$aksi];

        return back()->with('success', "Fase {$fase->tipe_label} {$pesan}.");
    }
}
