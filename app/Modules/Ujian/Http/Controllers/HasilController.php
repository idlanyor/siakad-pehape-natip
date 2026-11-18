<?php

namespace App\Modules\Ujian\Http\Controllers;

use App\Modules\Ujian\Exports\HasilUjianExport;
use App\Modules\Ujian\Models\JawabanSiswa;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\PenilaianService;
use App\Modules\Ujian\Support\TeksSoal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Hasil ujian untuk guru pemilik/admin: rekap peserta, detail jawaban,
 * koreksi essay, dan export.
 */
class HasilController extends Controller
{
    public function __construct(private readonly PenilaianService $penilaian) {}

    public function index(Ujian $ujian): Response
    {
        $this->authorize('nilai', $ujian);
        $ujian->load(['mapel:id,nama_mapel', 'kelas:id,nama_kelas']);
        $sesi = $this->rekap($ujian);
        $dinilai = $sesi->whereNotNull('nilai_akhir')->pluck('nilai_akhir')->map(fn ($n) => (float) $n);

        return Inertia::render('Hasil/Index', [
            'ujian' => [
                'id' => $ujian->id,
                'judul' => $ujian->judul,
                'mapel' => $ujian->mapel?->nama_mapel,
                'kelas' => $ujian->kelas?->nama_kelas,
                'passing_grade' => (float) $ujian->passing_grade,
            ],
            'sesi' => $sesi->map(fn (SesiUjian $s) => [
                'id' => $s->id,
                'siswa' => $s->siswa?->nama,
                'nisn' => $s->siswa?->nisn,
                'status' => $s->status,
                'is_resmi' => $s->is_resmi,
                'waktu_mulai' => $s->waktu_mulai,
                'waktu_selesai' => $s->waktu_selesai,
                'nilai_otomatis' => $s->nilai_otomatis,
                'nilai_manual' => $s->nilai_manual,
                'nilai_akhir' => $s->nilai_akhir,
                'predikat' => $s->predikat,
                'jumlah_pelanggaran' => $s->jumlah_pelanggaran,
                'perlu_koreksi' => (int) $s->essay_belum_dinilai_count > 0,
            ])->values(),
            'statistik' => [
                'peserta' => $sesi->count(),
                'rata_rata' => $dinilai->isEmpty() ? null : round($dinilai->avg(), 2),
                'tertinggi' => $dinilai->max(),
                'terendah' => $dinilai->min(),
                'lulus' => $dinilai->filter(fn ($n) => $n >= (float) $ujian->passing_grade)->count(),
                'perlu_koreksi' => $sesi->filter(fn ($s) => $s->essay_belum_dinilai_count > 0)->count(),
            ],
        ]);
    }

    public function sesi(SesiUjian $sesi): Response
    {
        $this->authorize('nilai', $sesi);
        $sesi->load(['ujian:id,judul,passing_grade', 'siswa:id,nama,nisn', 'jawaban.soal', 'proktorEvents.user:id,name']);

        return Inertia::render('Hasil/Sesi', [
            'sesi' => [
                'id' => $sesi->id,
                'ujian_id' => $sesi->ujian_id,
                'ujian' => $sesi->ujian->judul,
                'passing_grade' => (float) $sesi->ujian->passing_grade,
                'siswa' => $sesi->siswa?->nama,
                'nisn' => $sesi->siswa?->nisn,
                'status' => $sesi->status,
                'waktu_mulai' => $sesi->waktu_mulai,
                'waktu_selesai' => $sesi->waktu_selesai,
                'nilai_otomatis' => $sesi->nilai_otomatis,
                'nilai_manual' => $sesi->nilai_manual,
                'nilai_akhir' => $sesi->nilai_akhir,
                'predikat' => $sesi->predikat,
                'jumlah_pelanggaran' => $sesi->jumlah_pelanggaran,
            ],
            'jawaban' => $sesi->jawaban->sortBy(fn (JawabanSiswa $j) => $j->soal->nomor)->values()->map(fn (JawabanSiswa $j) => [
                'id' => $j->id,
                'nomor' => $j->soal->nomor,
                'tipe' => $j->soal->tipe,
                'pertanyaan_html' => TeksSoal::html($j->soal->pertanyaan),
                'opsi' => array_map(fn ($teks) => TeksSoal::inline($teks), $j->soal->opsi()),
                'kunci_jawaban' => $j->soal->kunci_jawaban,
                'bobot' => (float) $j->soal->bobot_nilai,
                'jawaban_pilihan' => $j->jawaban_pilihan,
                'jawaban_text' => $j->jawaban_text,
                'is_benar' => $j->is_benar,
                'nilai' => $j->nilai,
            ]),
            'event' => $sesi->proktorEvents->sortByDesc('created_at')->values()->map(fn ($e) => [
                'id' => $e->id,
                'label' => $e->tipe_label,
                'severity' => $e->severity,
                'keterangan' => $e->keterangan,
                'oleh' => $e->user?->name,
                'waktu' => $e->created_at,
            ]),
        ]);
    }

    public function nilaiEssay(Request $request, JawabanSiswa $jawaban): RedirectResponse
    {
        $this->authorize('nilai', $jawaban->sesi);
        $nilai = (float) $request->validate(['nilai' => ['required', 'numeric', 'min:0']])['nilai'];

        $this->penilaian->nilaiEssay($jawaban, $nilai);

        return back()->with('success', "Nilai soal nomor {$jawaban->soal->nomor} disimpan.");
    }

    public function excel(Ujian $ujian): BinaryFileResponse
    {
        $this->authorize('nilai', $ujian);

        return Excel::download(new HasilUjianExport($ujian, $this->rekap($ujian)), $this->namaFile($ujian).'.xlsx');
    }

    public function pdf(Ujian $ujian): \Illuminate\Http\Response
    {
        $this->authorize('nilai', $ujian);
        $ujian->load(['mapel:id,nama_mapel', 'kelas:id,nama_kelas', 'guru:id,nama']);

        return Pdf::loadView('ujian.hasil-pdf', ['ujian' => $ujian, 'sesi' => $this->rekap($ujian)])
            ->setPaper('a4', 'landscape')
            ->download($this->namaFile($ujian).'.pdf');
    }

    /**
     * Sesi ujian (resmi lebih dulu), nilai tertinggi di atas.
     */
    private function rekap(Ujian $ujian)
    {
        return $ujian->sesi()
            ->where('status', '!=', SesiUjian::STATUS_BELUM)
            ->with('siswa:id,nama,nisn')
            ->withCount(['jawaban as essay_belum_dinilai_count' => fn ($q) => $q
                ->whereNull('nilai')
                ->whereHas('soal', fn ($s) => $s->where('tipe', 'essay'))])
            ->orderByDesc('is_resmi')
            ->orderByDesc('nilai_akhir')
            ->get();
    }

    private function namaFile(Ujian $ujian): string
    {
        return 'hasil-'.Str::slug($ujian->judul).'-'.now()->format('Ymd');
    }
}
