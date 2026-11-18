<?php

namespace App\Modules\Ujian\Models\Concerns;

/**
 * Perilaku bersama soal (BankSoal dan SoalUjian): tipe soal dan opsi A–E.
 */
trait PunyaOpsiJawaban
{
    public const TIPE_PILIHAN_GANDA = 'pilihan_ganda';

    public const TIPE_ESSAY = 'essay';

    public const HURUF_OPSI = ['A', 'B', 'C', 'D', 'E'];

    public function isPilihanGanda(): bool
    {
        return $this->tipe === self::TIPE_PILIHAN_GANDA;
    }

    public function isEssay(): bool
    {
        return $this->tipe === self::TIPE_ESSAY;
    }

    /**
     * Opsi yang terisi, dengan kunci huruf kapital: ['A' => '...', 'B' => '...'].
     *
     * @return array<string, string>
     */
    public function opsi(): array
    {
        $opsi = [];

        foreach (self::HURUF_OPSI as $huruf) {
            $nilai = $this->{'opsi_'.strtolower($huruf)};
            if ($nilai !== null && $nilai !== '') {
                $opsi[$huruf] = $nilai;
            }
        }

        return $opsi;
    }
}
