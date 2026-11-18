<?php

namespace App\Modules\Ujian\Http\Controllers;

use App\Modules\Ujian\Http\Requests\SoalUjianRequest;
use App\Modules\Ujian\Models\BankSoal;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\PengelolaanUjianService;
use App\Modules\Ujian\Support\TeksSoal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Penyusunan soal sebuah ujian: tulis manual, ambil dari bank soal, urutkan.
 */
class SoalUjianController extends Controller
{
    public function __construct(private readonly PengelolaanUjianService $pengelolaan) {}

    public function index(Request $request, Ujian $ujian): Response
    {
        $this->authorize('update', $ujian);
        $ujian->load('soal');
        $terpasang = $ujian->soal->pluck('bank_soal_id')->filter()->all();

        $bank = BankSoalController::queryTerlihat($request)
            ->where('mapel_id', $ujian->mapel_id)
            ->whereKeyNot($terpasang)
            ->when($request->input('cari'), fn ($q, $v) => $q->where('pertanyaan', 'like', "%{$v}%"))
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (BankSoal $s) => [
                'id' => $s->id,
                'pertanyaan' => $s->pertanyaan,
                'tipe' => $s->tipe,
                'topik' => $s->topik,
                'tingkat_kesulitan' => $s->tingkat_kesulitan,
                'bobot' => (float) $s->bobot_default,
            ]);

        return Inertia::render('Ujian/Soal', [
            'ujian' => [
                'id' => $ujian->id,
                'judul' => $ujian->judul,
                'status' => $ujian->status,
                'terkunci' => $ujian->sesi()->exists(),
            ],
            'soal' => $ujian->soal->map(fn (SoalUjian $s) => [
                ...$s->only(['id', 'nomor', 'tipe', 'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e',
                    'kunci_jawaban', 'bank_soal_id']),
                'bobot' => (float) $s->bobot_nilai,
                'pertanyaan_html' => TeksSoal::html($s->pertanyaan),
                'opsi_html' => array_map(fn ($teks) => TeksSoal::inline($teks), $s->opsi()),
            ]),
            'bank' => $bank,
            'cari' => $request->input('cari'),
            'totalBobot' => (float) $ujian->soal->sum('bobot_nilai'),
        ]);
    }

    public function store(SoalUjianRequest $request, Ujian $ujian): RedirectResponse
    {
        $this->authorize('update', $ujian);
        $soal = $this->pengelolaan->tambahSoal($ujian, $request->atribut());

        return back()->with('success', "Soal nomor {$soal->nomor} ditambahkan.");
    }

    public function dariBank(Request $request, Ujian $ujian): RedirectResponse
    {
        $this->authorize('update', $ujian);
        $ids = $request->validate([
            'bank_soal_ids' => ['required', 'array', 'min:1'],
            'bank_soal_ids.*' => ['integer'],
        ])['bank_soal_ids'];

        // Hanya soal yang boleh dilihat user ini dan sesuai mapel ujian.
        $diizinkan = BankSoalController::queryTerlihat($request)
            ->where('mapel_id', $ujian->mapel_id)
            ->whereKey($ids)
            ->pluck('id')
            ->all();

        $jumlah = $this->pengelolaan->tambahDariBank($ujian, $diizinkan);

        return back()->with('success', "{$jumlah} soal ditambahkan dari bank soal.");
    }

    public function update(SoalUjianRequest $request, SoalUjian $soal): RedirectResponse
    {
        $this->authorize('update', $soal->ujian);
        $this->pengelolaan->ubahSoal($soal, $request->atribut());

        return back()->with('success', "Soal nomor {$soal->nomor} diperbarui.");
    }

    public function destroy(SoalUjian $soal): RedirectResponse
    {
        $this->authorize('update', $soal->ujian);
        $this->pengelolaan->hapusSoal($soal);

        return back()->with('success', 'Soal dihapus.');
    }

    public function urutan(Request $request, Ujian $ujian): RedirectResponse
    {
        $this->authorize('update', $ujian);
        $ids = $request->validate([
            'soal_ids' => ['required', 'array'],
            'soal_ids.*' => ['integer'],
        ])['soal_ids'];

        $this->pengelolaan->urutkanSoal($ujian, $ids);

        return back()->with('success', 'Urutan soal disimpan.');
    }
}
