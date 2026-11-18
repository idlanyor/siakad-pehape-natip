<?php

namespace App\Modules\Ujian\Services;

use App\Modules\Ujian\Models\JawabanSiswa;
use App\Modules\Ujian\Models\SesiUjian;

/**
 * Menyusun data sesi untuk halaman siswa (mengerjakan dan hasil).
 *
 * Saat sesi berlangsung, kunci jawaban, status benar/salah, dan nilai tidak
 * pernah dikirim ke browser.
 */
class PenyajiSesi
{
    public function __construct(
        private readonly SesiUjianService $sesiService,
        private readonly PenilaianService $penilaian,
    ) {}

    public function pengerjaan(SesiUjian $sesi): array
    {
        $sesi->loadMissing(['ujian', 'jawaban.soal']);

        return [
            'sesi' => [
                'id' => $sesi->id,
                'status' => $sesi->status,
                'sisa_detik' => $this->sesiService->sisaDetik($sesi),
                'jumlah_pelanggaran' => $sesi->jumlah_pelanggaran,
                'batas_pelanggaran' => (int) config('ujian.batas_pelanggaran'),
            ],
            'ujian' => [
                'id' => $sesi->ujian->id,
                'judul' => $sesi->ujian->judul,
                'durasi_menit' => $sesi->ujian->durasi_menit,
            ],
            'soal' => $sesi->jawaban->map(fn (JawabanSiswa $j) => $this->soal($j))->values()->all(),
            'progres' => $this->progres($sesi),
        ];
    }

    public function hasil(SesiUjian $sesi): array
    {
        $sesi->loadMissing(['ujian', 'jawaban.soal']);
        $tampilkanNilai = $sesi->ujian->tampilkan_nilai;

        return [
            'sesi' => [
                'id' => $sesi->id,
                'status' => $sesi->status,
                'waktu_mulai' => $sesi->waktu_mulai,
                'waktu_selesai' => $sesi->waktu_selesai,
                'jumlah_pelanggaran' => $sesi->jumlah_pelanggaran,
            ],
            'ujian' => [
                'id' => $sesi->ujian->id,
                'judul' => $sesi->ujian->judul,
                'passing_grade' => (float) $sesi->ujian->passing_grade,
            ],
            'tampilkan_nilai' => $tampilkanNilai,
            'nilai' => $tampilkanNilai ? [
                'akhir' => (float) $sesi->nilai_akhir,
                'predikat' => $sesi->predikat,
                'lulus' => (float) $sesi->nilai_akhir >= (float) $sesi->ujian->passing_grade,
                'menunggu_koreksi' => $this->penilaian->perluKoreksi($sesi),
            ] : null,
            'progres' => $this->progres($sesi),
        ];
    }

    private function soal(JawabanSiswa $jawaban): array
    {
        $soal = $jawaban->soal;

        return [
            'id' => $soal->id,
            'urutan' => $jawaban->urutan,
            'tipe' => $soal->tipe,
            'pertanyaan' => $soal->pertanyaan,
            'opsi' => $this->opsiTampil($jawaban),
            'jawaban_pilihan' => $jawaban->jawaban_pilihan,
            'jawaban_text' => $jawaban->jawaban_text,
            'is_ragu' => $jawaban->is_ragu,
        ];
    }

    /**
     * Opsi dalam urutan tampil. `kode` adalah huruf asli (dikirim balik saat
     * menjawab), `label` adalah huruf yang dilihat siswa.
     *
     * @return list<array{kode: string, label: string, teks: string}>
     */
    private function opsiTampil(JawabanSiswa $jawaban): array
    {
        $opsi = $jawaban->soal->opsi();
        if ($opsi === []) {
            return [];
        }

        $urutan = $jawaban->urutan_opsi ? str_split($jawaban->urutan_opsi) : array_keys($opsi);
        $label = $jawaban->soal::HURUF_OPSI;

        return collect($urutan)
            ->filter(fn (string $kode) => isset($opsi[$kode]))
            ->values()
            ->map(fn (string $kode, int $i) => ['kode' => $kode, 'label' => $label[$i], 'teks' => $opsi[$kode]])
            ->all();
    }

    private function progres(SesiUjian $sesi): array
    {
        return [
            'total' => $sesi->jawaban->count(),
            'terjawab' => $sesi->jawaban->filter->isTerjawab()->count(),
            'ragu' => $sesi->jawaban->where('is_ragu', true)->count(),
        ];
    }
}
