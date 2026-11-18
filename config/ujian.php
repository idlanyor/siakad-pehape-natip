<?php

return [

    /*
    | Jumlah pelanggaran (pindah tab, copy, devtools, dst.) sebelum sesi siswa
    | otomatis didiskualifikasi. 0 = tidak pernah otomatis.
    */
    'batas_pelanggaran' => (int) env('UJIAN_BATAS_PELANGGARAN', 5),

    /*
    | Ambang status koneksi peserta di monitor proktor, berdasarkan aktivitas
    | terakhir (dalam detik).
    */
    'koneksi' => [
        'online' => 120,
        'idle' => 300,
    ],

];
