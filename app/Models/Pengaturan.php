<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Pengaturan extends Model
{
    protected $fillable = ['key', 'value', 'grup', 'label', 'tipe'];

    const CACHE_KEY = 'pengaturan_all';
    const CACHE_TTL = 300; // 5 menit

    // ─── Grupan konfigurasi ────────────────────────────────────────────────────

    const GRUPAN = [
        'sekolah'  => 'Identitas Sekolah',
        'media'    => 'Logo & Media',
        'sosmed'   => 'Media Sosial',
        'kepsek'   => 'Kepala Sekolah',
        'ppdb'     => 'Pengaturan PPDB',
    ];

    // ─── Default settings (digunakan saat seeder) ─────────────────────────────

    const DEFAULTS = [
        // Sekolah
        ['key' => 'sekolah_nama',     'label' => 'Nama Sekolah',       'grup' => 'sekolah', 'tipe' => 'text',     'value' => 'SMK Negeri 48 Jakarta'],
        ['key' => 'sekolah_tagline',  'label' => 'Tagline',            'grup' => 'sekolah', 'tipe' => 'text',     'value' => 'Unggul, Berkarakter, dan Berdaya Saing Global'],
        ['key' => 'sekolah_alamat',   'label' => 'Alamat',             'grup' => 'sekolah', 'tipe' => 'textarea', 'value' => 'Jl. Raya Condet No. 1, Jakarta Timur, DKI Jakarta 13760'],
        ['key' => 'sekolah_no_telp',  'label' => 'No. Telepon',        'grup' => 'sekolah', 'tipe' => 'text',     'value' => '(021) 8001234'],
        ['key' => 'sekolah_email',    'label' => 'Email',              'grup' => 'sekolah', 'tipe' => 'text',     'value' => 'info@smkn48.sch.id'],
        ['key' => 'sekolah_website',  'label' => 'Website',            'grup' => 'sekolah', 'tipe' => 'text',     'value' => 'https://smkn48.sch.id'],
        ['key' => 'sekolah_tahun_berdiri', 'label' => 'Tahun Berdiri', 'grup' => 'sekolah', 'tipe' => 'text',    'value' => '1975'],
        ['key' => 'sekolah_npsn',     'label' => 'NPSN',               'grup' => 'sekolah', 'tipe' => 'text',     'value' => '20100001'],
        ['key' => 'maps_embed',       'label' => 'Google Maps Embed URL', 'grup' => 'sekolah', 'tipe' => 'text',  'value' => ''],
        // Media
        ['key' => 'logo',             'label' => 'Logo (warna)',        'grup' => 'media',   'tipe' => 'image',    'value' => null],
        ['key' => 'logo_putih',       'label' => 'Logo (putih/transparan)', 'grup' => 'media', 'tipe' => 'image', 'value' => null],
        ['key' => 'favicon',          'label' => 'Favicon',            'grup' => 'media',   'tipe' => 'image',    'value' => null],
        ['key' => 'hero_bg',          'label' => 'Background Hero',    'grup' => 'media',   'tipe' => 'image',    'value' => null],
        // Sosmed
        ['key' => 'sosmed_instagram', 'label' => 'Instagram URL',      'grup' => 'sosmed',  'tipe' => 'text',     'value' => ''],
        ['key' => 'sosmed_facebook',  'label' => 'Facebook URL',       'grup' => 'sosmed',  'tipe' => 'text',     'value' => ''],
        ['key' => 'sosmed_youtube',   'label' => 'YouTube URL',        'grup' => 'sosmed',  'tipe' => 'text',     'value' => ''],
        ['key' => 'sosmed_tiktok',    'label' => 'TikTok URL',         'grup' => 'sosmed',  'tipe' => 'text',     'value' => ''],
        // Kepsek
        ['key' => 'nama_kepsek',      'label' => 'Nama Kepala Sekolah','grup' => 'kepsek',  'tipe' => 'text',     'value' => ''],
        ['key' => 'nip_kepsek',       'label' => 'NIP Kepala Sekolah', 'grup' => 'kepsek',  'tipe' => 'text',     'value' => ''],
        ['key' => 'foto_kepsek',      'label' => 'Foto Kepala Sekolah','grup' => 'kepsek',  'tipe' => 'image',    'value' => null],
        ['key' => 'sambutan_kepsek',  'label' => 'Sambutan',           'grup' => 'kepsek',  'tipe' => 'richtext', 'value' => '<p>Selamat datang di website resmi SMK Negeri 48 Jakarta.</p>'],
        // PPDB
        ['key' => 'ppdb_aktif',       'label' => 'PPDB Aktif',         'grup' => 'ppdb',    'tipe' => 'boolean',  'value' => '0'],
        ['key' => 'ppdb_tahun_ajaran','label' => 'Tahun Ajaran PPDB',  'grup' => 'ppdb',    'tipe' => 'text',     'value' => '2026/2027'],
        ['key' => 'ppdb_info',        'label' => 'Info PPDB (banner)', 'grup' => 'ppdb',    'tipe' => 'textarea', 'value' => ''],
    ];

    // ─── Static Helper ─────────────────────────────────────────────────────────

    /**
     * Ambil nilai pengaturan berdasarkan key. Di-cache 5 menit.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return static::pluck('value', 'key')->all();
        });

        return $all[$key] ?? $default;
    }

    /**
     * Ambil semua pengaturan sebagai array key-value (cached).
     */
    public static function all_cached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return static::pluck('value', 'key')->all();
        });
    }

    /**
     * Hapus cache setelah update.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
