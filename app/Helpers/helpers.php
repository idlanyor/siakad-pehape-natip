<?php

if (! function_exists('setting')) {
    /**
     * Ambil nilai pengaturan sekolah.
     * Contoh: setting('sekolah_nama'), setting('logo')
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\Pengaturan::get($key, $default);
    }
}

if (! function_exists('setting_url')) {
    /**
     * Ambil URL file dari pengaturan (logo, foto, dll).
     * Contoh: setting_url('logo') → asset('storage/cms/logo.png')
     */
    function setting_url(string $key): ?string
    {
        $val = \App\Models\Pengaturan::get($key);
        return $val ? asset('storage/' . $val) : null;
    }
}
