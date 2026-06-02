<?php

namespace App\Support;

use App\Models\SesiUjian;
use Carbon\Carbon;

class SesiPresenter
{
    private const STATUS_BERLANGSUNG = ['berlangsung', 'sedang_mengerjakan'];

    /**
     * Susun data soal + progress + sisa waktu untuk halaman pengerjaan.
     * Kunci jawaban hanya disertakan jika sesi sudah selesai.
     */
    public function forPengerjaan(SesiUjian $sesi): array
    {
        $sesi->loadMissing(['ujian', 'jawabans.soal']);
        $berlangsung = in_array($sesi->status, self::STATUS_BERLANGSUNG, true);

        $soals = $sesi->jawabans->map(function ($jawaban) use ($sesi, $berlangsung) {
            $soal = $jawaban->soal;

            $opsi = [
                'A' => $soal->opsi_a, 'B' => $soal->opsi_b, 'C' => $soal->opsi_c,
                'D' => $soal->opsi_d, 'E' => $soal->opsi_e,
            ];
            if ($soal->tipe === 'pilihan_ganda' && $sesi->ujian->acak_opsi) {
                $opsi = collect($opsi)->filter()->shuffle()->toArray();
            }

            return [
                'id' => $soal->id,
                'nomor' => $soal->nomor,
                'tipe' => $soal->tipe,
                'pertanyaan' => $soal->pertanyaan,
                'opsi' => $opsi,
                'media_url' => $soal->media_url ?? null,
                'jawaban_id' => $jawaban->id,
                'jawaban' => $jawaban->jawaban,
                'is_ragu' => (bool) $jawaban->is_ragu,
                'kunci_jawaban' => $berlangsung ? null : $soal->kunci_jawaban,
                'is_benar' => $jawaban->is_benar,
                'nilai' => $jawaban->nilai,
            ];
        })->values()->all();

        $durasi = $sesi->durasi_detik > 0
            ? (int) $sesi->durasi_detik
            : (int) ($sesi->ujian->durasi_menit * 60);
        $waktuBerakhir = Carbon::parse($sesi->waktu_mulai)->addSeconds($durasi);
        $sisa = $berlangsung ? max(0, (int) now()->diffInSeconds($waktuBerakhir, false)) : 0;

        return [
            'soals' => $soals,
            'progress' => [
                'terjawab' => $sesi->jawabans->whereNotNull('jawaban')->count(),
                'ragu' => $sesi->jawabans->where('is_ragu', true)->count(),
                'total' => $sesi->jawabans->count(),
            ],
            'sisa_waktu_detik' => $sisa,
        ];
    }
}
