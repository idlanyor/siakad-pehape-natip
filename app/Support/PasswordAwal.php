<?php

namespace App\Support;

/**
 * Password awal untuk akun yang dibuat sistem (impor siswa/guru, pendaftar PPDB).
 *
 * Acak dan tidak bisa ditebak; jangan memakai NISN/NIP karena keduanya bukan rahasia.
 * Tanpa karakter yang mirip (l/1/I, o/0/O) agar mudah disalin dari kertas.
 */
class PasswordAwal
{
    private const ABJAD = 'abcdefghjkmnpqrstuvwxyz23456789';

    public static function buat(int $panjang = 10): string
    {
        $hasil = '';
        for ($i = 0; $i < $panjang; $i++) {
            $hasil .= self::ABJAD[random_int(0, strlen(self::ABJAD) - 1)];
        }

        return $hasil;
    }
}
