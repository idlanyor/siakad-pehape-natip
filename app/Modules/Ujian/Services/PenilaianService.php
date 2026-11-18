<?php

namespace App\Modules\Ujian\Services;

use App\Models\Guru;
use App\Modules\Ujian\Exceptions\UjianException;
use App\Modules\Ujian\Models\JawabanSiswa;
use App\Modules\Ujian\Models\SesiUjian;
use App\Services\ELearningNotifier;
use Illuminate\Support\Facades\DB;

/**
 * Penilaian sesi ujian.
 *
 * Skala 0–100 terhadap total bobot seluruh soal di sesi:
 * - nilai_otomatis: poin pilihan ganda (dikoreksi otomatis)
 * - nilai_manual:   poin essay (dikoreksi guru)
 * - nilai_akhir:    nilai_otomatis + nilai_manual
 *
 * Versi ujian-service menghitung bobot essay di penyebut tetapi tidak pernah
 * menambahkan nilai essay, sehingga nilai siswa terpotong bila ada essay.
 */
class PenilaianService
{
    /**
     * Koreksi otomatis semua jawaban pilihan ganda, lalu hitung ulang nilai sesi.
     */
    public function koreksiOtomatis(SesiUjian $sesi): void
    {
        $sesi->loadMissing('jawaban.soal');

        foreach ($sesi->jawaban as $jawaban) {
            if (! $jawaban->soal->isPilihanGanda()) {
                continue;
            }

            $benar = $jawaban->jawaban_pilihan !== null
                && $jawaban->jawaban_pilihan === $jawaban->soal->kunci_jawaban;

            $jawaban->update([
                'is_benar' => $benar,
                'nilai' => $benar ? $jawaban->soal->bobot_nilai : 0,
            ]);
        }

        $this->hitungNilaiSesi($sesi);
    }

    /**
     * Nilai manual satu jawaban essay (0 sampai bobot soal), lalu hitung ulang nilai sesi.
     */
    public function nilaiEssay(JawabanSiswa $jawaban, float $nilai): void
    {
        $soal = $jawaban->soal;

        if (! $soal->isEssay()) {
            throw UjianException::bukanEssay();
        }

        $maks = (float) $soal->bobot_nilai;
        if ($nilai < 0 || $nilai > $maks) {
            throw UjianException::nilaiDiLuarRentang($maks);
        }

        $sesi = $jawaban->sesi;
        $masihAdaYangBelum = $this->perluKoreksi($sesi);

        DB::transaction(function () use ($jawaban, $nilai, $sesi) {
            $jawaban->update(['nilai' => $nilai]);
            $this->hitungNilaiSesi($sesi);
        });

        // Beri tahu siswa sekali, saat essay terakhir yang belum dinilai selesai dikoreksi.
        if ($masihAdaYangBelum && $sesi->isBerakhir() && ! $this->perluKoreksi($sesi)) {
            ELearningNotifier::ujianDikoreksi($sesi->refresh());
        }
    }

    public function hitungNilaiSesi(SesiUjian $sesi): void
    {
        $sesi->load('jawaban.soal');

        $totalBobot = 0.0;
        $poinOtomatis = 0.0;
        $poinManual = 0.0;

        foreach ($sesi->jawaban as $jawaban) {
            $totalBobot += (float) $jawaban->soal->bobot_nilai;

            if ($jawaban->soal->isPilihanGanda()) {
                $poinOtomatis += (float) $jawaban->nilai;
            } else {
                $poinManual += (float) $jawaban->nilai;
            }
        }

        $skala = fn (float $poin) => $totalBobot > 0 ? round($poin / $totalBobot * 100, 2) : 0.0;

        $nilaiAkhir = $sesi->status === SesiUjian::STATUS_DISKUALIFIKASI
            ? 0.0
            : $skala($poinOtomatis + $poinManual);

        $sesi->update([
            'nilai_otomatis' => $skala($poinOtomatis),
            'nilai_manual' => $skala($poinManual),
            'nilai_akhir' => $nilaiAkhir,
            'predikat' => self::predikat($nilaiAkhir),
        ]);
    }

    /**
     * Sama dengan ambang predikat di App\Models\Nilai.
     */
    public static function predikat(?float $nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        return match (true) {
            $nilai >= 90 => 'A',
            $nilai >= 80 => 'B',
            $nilai >= 70 => 'C',
            default => 'D',
        };
    }

    /**
     * Jumlah jawaban essay di ujian milik guru yang sudah dikumpulkan tetapi
     * belum dinilai (badge sidebar & dashboard guru SIAKAD).
     */
    public static function jumlahPerluKoreksi(Guru $guru): int
    {
        return JawabanSiswa::query()
            ->whereNull('nilai')
            ->whereHas('soal', fn ($q) => $q->where('tipe', 'essay'))
            ->whereHas('sesi', fn ($q) => $q
                ->whereIn('status', SesiUjian::STATUS_BERAKHIR)
                ->whereHas('ujian', fn ($u) => $u->where('guru_id', $guru->id)))
            ->count();
    }

    /**
     * Masih ada jawaban essay yang belum dinilai guru.
     */
    public function perluKoreksi(SesiUjian $sesi): bool
    {
        return $sesi->jawaban()
            ->whereNull('nilai')
            ->whereHas('soal', fn ($q) => $q->where('tipe', 'essay'))
            ->exists();
    }
}
