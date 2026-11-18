<?php

/*
| Ziggy hanya mengekspos route yang dibutuhkan halaman Vue modul ujian,
| supaya daftar seluruh route SIAKAD tidak ikut terkirim ke browser.
*/

return [
    'groups' => [
        'ujian' => ['ujian.*', 'dashboard', 'logout'],
    ],
];
