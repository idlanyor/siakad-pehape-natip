<?php

namespace App\Modules\Ujian\Exceptions;

use DomainException;

/**
 * Aturan bisnis ujian dilanggar (ujian belum dibuka, waktu habis, dsb).
 * Pesannya aman ditampilkan ke pengguna; controller memetakannya ke
 * respons 422 / flash error.
 */
class UjianException extends DomainException
{
    public static function belumDipublikasikan(): self
    {
        return new self('Ujian belum dipublikasikan.');
    }

    public static function diLuarJadwal(): self
    {
        return new self('Ujian tidak sedang dalam waktu pelaksanaan.');
    }

    public static function bukanKelasSiswa(): self
    {
        return new self('Ujian ini tidak untuk kelas Anda.');
    }

    public static function sudahSelesai(): self
    {
        return new self('Anda sudah menyelesaikan ujian ini.');
    }

    public static function tanpaSoal(): self
    {
        return new self('Ujian belum memiliki soal.');
    }

    public static function sesiTidakAktif(): self
    {
        return new self('Sesi ujian tidak sedang berlangsung.');
    }

    public static function waktuHabis(): self
    {
        return new self('Waktu ujian telah habis.');
    }

    public static function soalBukanBagianSesi(): self
    {
        return new self('Soal tidak termasuk dalam sesi ujian ini.');
    }

    public static function opsiTidakValid(): self
    {
        return new self('Pilihan jawaban tidak valid.');
    }

    public static function bukanEssay(): self
    {
        return new self('Hanya jawaban essay yang dinilai manual.');
    }

    public static function nilaiDiLuarRentang(float $maks): self
    {
        return new self("Nilai harus di antara 0 dan {$maks}.");
    }
}
