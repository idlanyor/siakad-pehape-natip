<?php

namespace App\Modules\Ujian\Http\Controllers;

use App\Modules\Ujian\Http\Requests\BankSoalRequest;
use App\Modules\Ujian\Models\BankSoal;
use App\Modules\Ujian\Support\OpsiForm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankSoalController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', BankSoal::class);
        $filter = $request->only(['mapel_id', 'tipe', 'tingkat_kesulitan', 'cari']);

        $soal = $this->queryTerlihat($request)
            ->with(['mapel:id,nama_mapel', 'pembuat:id,name'])
            ->when($filter['mapel_id'] ?? null, fn ($q, $v) => $q->where('mapel_id', $v))
            ->when($filter['tipe'] ?? null, fn ($q, $v) => $q->where('tipe', $v))
            ->when($filter['tingkat_kesulitan'] ?? null, fn ($q, $v) => $q->where('tingkat_kesulitan', $v))
            ->when($filter['cari'] ?? null, fn ($q, $v) => $q->where(fn ($w) => $w
                ->where('pertanyaan', 'like', "%{$v}%")->orWhere('topik', 'like', "%{$v}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (BankSoal $s) => [
                'id' => $s->id,
                'pertanyaan' => $s->pertanyaan,
                'tipe' => $s->tipe,
                'topik' => $s->topik,
                'tingkat_kesulitan' => $s->tingkat_kesulitan,
                'mapel' => $s->mapel?->nama_mapel,
                'pembuat' => $s->pembuat?->name,
                'bobot' => (float) $s->bobot_default,
                'bisa_ubah' => $request->user()->can('update', $s),
            ]);

        return Inertia::render('BankSoal/Index', [
            'soal' => $soal,
            'filter' => $filter,
            'mapel' => OpsiForm::mapel($request->user()),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', BankSoal::class);

        return Inertia::render('BankSoal/Form', [
            'soal' => null,
            'mapel' => OpsiForm::mapel($request->user()),
        ]);
    }

    public function store(BankSoalRequest $request): RedirectResponse
    {
        $this->authorize('create', BankSoal::class);
        BankSoal::create([...$request->atribut(), 'created_by' => $request->user()->id]);

        return to_route('ujian.bank-soal.index')->with('success', 'Soal ditambahkan ke bank soal.');
    }

    public function edit(Request $request, BankSoal $bankSoal): Response
    {
        $this->authorize('update', $bankSoal);

        return Inertia::render('BankSoal/Form', [
            'soal' => [
                ...$bankSoal->only(['id', 'mapel_id', 'topik', 'tingkat_kesulitan', 'tipe', 'pertanyaan',
                    'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'kunci_jawaban']),
                'tag' => $bankSoal->tag ?? [],
                'bobot' => (float) $bankSoal->bobot_default,
            ],
            'mapel' => OpsiForm::mapel($request->user()),
        ]);
    }

    public function update(BankSoalRequest $request, BankSoal $bankSoal): RedirectResponse
    {
        $this->authorize('update', $bankSoal);
        $bankSoal->update($request->atribut());

        return to_route('ujian.bank-soal.index')->with('success', 'Soal diperbarui. Ujian yang sudah memakai soal ini tidak berubah.');
    }

    public function destroy(BankSoal $bankSoal): RedirectResponse
    {
        $this->authorize('delete', $bankSoal);
        $bankSoal->delete();

        return back()->with('success', 'Soal dihapus dari bank soal.');
    }

    /**
     * Admin: semua. Guru: soal buatannya dan soal mapel yang diampu (sama dengan BankSoalPolicy::view).
     */
    public static function queryTerlihat(Request $request): Builder
    {
        $user = $request->user();
        $query = BankSoal::query();

        if ($user->isAdmin()) {
            return $query;
        }

        $mapelIds = $user->guru?->mapels()->pluck('mapels.id')->all() ?? [];

        return $query->where(fn ($q) => $q->where('created_by', $user->id)->orWhereIn('mapel_id', $mapelIds));
    }
}
