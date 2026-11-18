<?php

namespace App\Modules\Ujian\Support;

use Illuminate\Support\Str;

/**
 * Render teks soal/opsi (Markdown) menjadi HTML yang aman ditampilkan.
 *
 * - HTML mentah di-escape (tidak ada <script>, atribut on*, dsb).
 * - Link javascript:/data: dibuang oleh CommonMark (allow_unsafe_links=false).
 * - Gambar hanya boleh dari folder unggahan soal milik aplikasi sendiri,
 *   supaya soal tidak bisa memuat gambar pelacak dari situs luar.
 */
class TeksSoal
{
    public const FOLDER = 'ujian/soal';

    private const OPSI = ['html_input' => 'escape', 'allow_unsafe_links' => false, 'max_nesting_level' => 20];

    public static function html(?string $teks): string
    {
        if ($teks === null || trim($teks) === '') {
            return '';
        }

        return trim(self::saringGambar(Str::markdown($teks, self::OPSI)));
    }

    /** Untuk opsi jawaban: tanpa pembungkus <p>. */
    public static function inline(?string $teks): string
    {
        if ($teks === null || trim($teks) === '') {
            return '';
        }

        // inlineMarkdown tidak merender gambar; opsi bergambar memakai blok biasa.
        $html = str_contains($teks, '![') ? Str::markdown($teks, self::OPSI) : Str::inlineMarkdown($teks, self::OPSI);

        return trim(self::saringGambar($html));
    }

    public static function urlDiizinkan(string $src): bool
    {
        $path = parse_url($src, PHP_URL_PATH) ?? '';
        $host = parse_url($src, PHP_URL_HOST);
        $hostApp = parse_url((string) config('app.url'), PHP_URL_HOST);

        return str_starts_with($path, '/storage/'.self::FOLDER.'/')
            && ! str_contains($path, '..')
            && ($host === null || $host === $hostApp);
    }

    private static function saringGambar(string $html): string
    {
        return preg_replace_callback('/<img\b[^>]*>/i', function (array $m) {
            if (! preg_match('/\bsrc="([^"]*)"/i', $m[0], $src) || ! self::urlDiizinkan(html_entity_decode($src[1]))) {
                return '';
            }

            // Tambahkan class supaya gambar menyesuaikan lebar kolom.
            return str_replace('<img ', '<img class="img-fluid rounded my-1" loading="lazy" ', $m[0]);
        }, $html) ?? '';
    }
}
