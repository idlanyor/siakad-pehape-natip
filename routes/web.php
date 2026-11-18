<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// --- Fase A: Midtrans Webhook (bypass CSRF) ---
Route::post('/webhook/midtrans', [\App\Http\Controllers\MidtransWebhookController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('webhook.midtrans');

// --- Installer Wizard (terkunci jika sudah terpasang) ---
Route::middleware('install.guard')->prefix('install')->name('install.')->group(function () {
    Route::get('/',          [\App\Http\Controllers\InstallController::class, 'welcome'])->name('welcome');
    Route::get('/database',  [\App\Http\Controllers\InstallController::class, 'database'])->name('database');
    Route::post('/database', [\App\Http\Controllers\InstallController::class, 'saveDatabase'])->name('database.save');
    Route::get('/admin',     [\App\Http\Controllers\InstallController::class, 'admin'])->name('admin');
    Route::post('/finish',   [\App\Http\Controllers\InstallController::class, 'finish'])->name('finish');
});

// --- Fase 15: Sitemap (Public) ---
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// --- Fase 15: Form Kontak (Public, POST only) ---
Route::post('/kontak', [\App\Http\Controllers\KontakController::class, 'store'])->name('kontak.store');

// --- Halaman depan: login siswa dengan NIS (tamu) atau ke dashboard (sudah login) ---
// Satu definisi untuk "/": sebelumnya ada dua, dan yang kedua menimpa yang pertama
// sehingga halaman login NIS tidak pernah tampil.
Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : app(\App\Http\Controllers\Auth\UjianLoginController::class)->create()
)->name('ujian.login');
Route::post('/masuk-ujian', [\App\Http\Controllers\Auth\UjianLoginController::class, 'store'])
    ->middleware('guest')->name('ujian.login.store');

// --- Fase 12: Landing Page (Public) ---
Route::get('/landing', [\App\Http\Controllers\LandingController::class, 'index'])->name('landing.index');
Route::get('/tentang', [\App\Http\Controllers\LandingController::class, 'tentang'])->name('landing.tentang');
Route::get('/program', [\App\Http\Controllers\LandingController::class, 'program'])->name('landing.program');
Route::get('/organisasi', [\App\Http\Controllers\LandingController::class, 'organisasi'])->name('landing.organisasi');
Route::get('/halaman/{halaman:slug}', [\App\Http\Controllers\LandingController::class, 'halaman'])->name('landing.halaman');

// --- Fase 13: Berita & Galeri (Public) ---
Route::get('/berita', [\App\Http\Controllers\BeritaController::class, 'index'])->name('berita.index');
Route::get('/berita/{artikel:slug}', [\App\Http\Controllers\BeritaController::class, 'show'])->name('berita.show');
Route::get('/galeri', [\App\Http\Controllers\GaleriPublikController::class, 'index'])->name('galeri.index');
Route::get('/galeri/{galeri}', [\App\Http\Controllers\GaleriPublikController::class, 'show'])->name('galeri.show');

// --- Fase 9 + 14: PPDB (Public — tanpa login) ---
Route::prefix('ppdb')->name('ppdb.')->group(function () {
    Route::get('/',          [\App\Http\Controllers\PpdbController::class, 'landing'])->name('landing');
    Route::get('/daftar',    [\App\Http\Controllers\PpdbController::class, 'index'])->name('index');
    Route::post('/daftar',   [\App\Http\Controllers\PpdbController::class, 'store'])->name('store');
    Route::get('/sukses/{pendaftar}',       [\App\Http\Controllers\PpdbController::class, 'sukses'])->name('sukses');
    Route::get('/sukses/{pendaftar}/cetak', [\App\Http\Controllers\PpdbController::class, 'cetakBukti'])->name('cetak');
    Route::get('/cek',       [\App\Http\Controllers\PpdbController::class, 'cekForm'])->name('cek');
    Route::post('/cek',      [\App\Http\Controllers\PpdbController::class, 'cekStatus'])->name('cek.status');
    Route::get('/syarat',    [\App\Http\Controllers\PpdbController::class, 'syarat'])->name('syarat');
});

Route::get('/dashboard', function () {
    if (auth()->user()->role == 0) {
        return redirect()->route('admin.dashboard');
    }
    if (auth()->user()->role == 1) {
        return redirect()->route('guru.dashboard');
    }
    if (auth()->user()->role == 3) {
        return redirect()->route('pendaftar.dashboard');
    }
    if (auth()->user()->role == 4) {
        return redirect()->route('orangtua.dashboard');
    }
    return redirect()->route('siswa.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// --- PPDB: Dashboard Pendaftar (role=3) ---
Route::middleware(['auth', 'role:3'])->prefix('pendaftar')->name('pendaftar.')->group(function () {
    Route::get('/dashboard',          [\App\Http\Controllers\PendaftarController::class, 'dashboard'])->name('dashboard');
    Route::post('/dokumen/{field}',   [\App\Http\Controllers\PendaftarController::class, 'uploadDokumen'])->name('dokumen.upload');
    Route::delete('/dokumen/{field}', [\App\Http\Controllers\PendaftarController::class, 'hapusDokumen'])->name('dokumen.hapus');
    Route::get('/cetak',              [\App\Http\Controllers\PendaftarController::class, 'cetakBukti'])->name('cetak');
});

Route::middleware(['auth', 'role:0'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');

    Route::resource('jurusan', \App\Http\Controllers\Admin\JurusanController::class)->except(['show']);
    Route::resource('kelas', \App\Http\Controllers\Admin\KelasController::class)->except(['show'])->parameters(['kelas' => 'kela']);
    Route::resource('mapel', \App\Http\Controllers\Admin\MapelController::class)->except(['show']);
    Route::resource('guru', \App\Http\Controllers\Admin\GuruController::class)->except(['show']);
    Route::delete('guru-bulk', [\App\Http\Controllers\Admin\GuruController::class, 'bulkDestroy'])->name('guru.bulk-destroy');
    Route::get('guru/{guru}/permission', [\App\Http\Controllers\Admin\GuruPermissionController::class, 'edit'])->name('guru.permission.edit');
    Route::put('guru/{guru}/permission', [\App\Http\Controllers\Admin\GuruPermissionController::class, 'update'])->name('guru.permission.update');
    Route::resource('siswa', \App\Http\Controllers\Admin\SiswaAdminController::class)->except(['show']);
    Route::delete('siswa-bulk', [\App\Http\Controllers\Admin\SiswaAdminController::class, 'bulkDestroy'])->name('siswa.bulk-destroy');

    Route::resource('jadwal', \App\Http\Controllers\Admin\JadwalController::class)->except(['show']);
    Route::get('/absensi', [\App\Http\Controllers\Admin\AbsensiController::class, 'index'])->name('absensi.index');
    Route::get('/absensi/{jadwal}', [\App\Http\Controllers\Admin\AbsensiController::class, 'detail'])->name('absensi.detail');
    Route::get('/absensi/{jadwal}/export', [\App\Http\Controllers\Admin\AbsensiController::class, 'exportAbsensi'])->name('absensi.export');

    Route::get('/nilai', [\App\Http\Controllers\Admin\NilaiController::class, 'index'])->name('nilai.index');
    Route::get('/nilai/{jadwal}', [\App\Http\Controllers\Admin\NilaiController::class, 'detail'])->name('nilai.detail');
    Route::get('/nilai/{jadwal}/export', [\App\Http\Controllers\Admin\NilaiController::class, 'exportNilai'])->name('nilai.export');
    Route::get('/raport', [\App\Http\Controllers\Admin\NilaiController::class, 'raport'])->name('raport');
    Route::get('/raport/pdf', [\App\Http\Controllers\Admin\NilaiController::class, 'raportPdf'])->name('raport.pdf');


    Route::resource('pkl/tempat', \App\Http\Controllers\Admin\TempatPklController::class)->except(['show'])->parameters(['tempat' => 'pklTempat'])->names([
        'index' => 'pkl-tempat.index', 'create' => 'pkl-tempat.create', 'store' => 'pkl-tempat.store',
        'edit' => 'pkl-tempat.edit', 'update' => 'pkl-tempat.update', 'destroy' => 'pkl-tempat.destroy',
    ]);
    Route::resource('pkl/penempatan', \App\Http\Controllers\Admin\PenempatanPklController::class)->except(['show'])->parameters(['penempatan' => 'pklPenempatan'])->names([
        'index' => 'pkl-penempatan.index', 'create' => 'pkl-penempatan.create', 'store' => 'pkl-penempatan.store',
        'edit' => 'pkl-penempatan.edit', 'update' => 'pkl-penempatan.update', 'destroy' => 'pkl-penempatan.destroy',
    ]);
    Route::get('/pkl', [\App\Http\Controllers\Admin\PklController::class, 'index'])->name('pkl.index');
    Route::get('/pkl/{penempatan}', [\App\Http\Controllers\Admin\PklController::class, 'detail'])->name('pkl.detail');

    // --- Kesiswaan ---
    Route::resource('pelanggaran', \App\Http\Controllers\Admin\PelanggaranController::class)->except(['show']);
    Route::resource('prestasi', \App\Http\Controllers\Admin\PrestasiController::class)->except(['show']);
    Route::resource('ekskul', \App\Http\Controllers\Admin\EkskulController::class)->except(['show']);
    Route::get('ekskul/{ekskul}/anggota', [\App\Http\Controllers\Admin\EkskulController::class, 'anggota'])->name('ekskul.anggota');
    Route::post('ekskul/{ekskul}/anggota', [\App\Http\Controllers\Admin\EkskulController::class, 'anggotaStore'])->name('ekskul.anggota.store');
    Route::delete('ekskul/{ekskul}/anggota/{anggota}', [\App\Http\Controllers\Admin\EkskulController::class, 'anggotaDestroy'])->name('ekskul.anggota.destroy');
    Route::resource('surat', \App\Http\Controllers\Admin\SuratController::class)->except(['show']);
    Route::get('surat/{surat}/print', [\App\Http\Controllers\Admin\SuratController::class, 'print'])->name('surat.print');

    Route::resource('tahun-ajaran', \App\Http\Controllers\Admin\TahunAjaranController::class)->except(['show'])->parameters(['tahun-ajaran' => 'tahunAjaran']);
    Route::post('tahun-ajaran/{tahunAjaran}/aktif', [\App\Http\Controllers\Admin\TahunAjaranController::class, 'setAktif'])->name('tahun-ajaran.aktif');
    Route::post('semester/{semester}/aktif', [\App\Http\Controllers\Admin\TahunAjaranController::class, 'setSemesterAktif'])->name('semester.aktif');

    // --- Import Excel ---
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ImportController::class, 'index'])->name('index');
        Route::get('/template/{type}', [\App\Http\Controllers\Admin\ImportController::class, 'template'])->name('template');
        Route::post('/', [\App\Http\Controllers\Admin\ImportController::class, 'store'])->name('store');
    });

    // --- Keuangan / SPP ---
    Route::prefix('keuangan')->name('keuangan.')->group(function () {
        Route::resource('tarif', \App\Http\Controllers\Admin\TarifSppController::class)->except(['show'])->parameters(['tarif' => 'tarif']);
        Route::get('tagihan/export', [\App\Http\Controllers\Admin\TagihanSppController::class, 'export'])->name('tagihan.export');
        Route::resource('tagihan', \App\Http\Controllers\Admin\TagihanSppController::class)->except(['edit', 'update'])->parameters(['tagihan' => 'tagihan']);
        Route::post('tagihan/{tagihan}/bayar', [\App\Http\Controllers\Admin\PembayaranSppController::class, 'store'])->name('tagihan.bayar');
        Route::delete('pembayaran/{pembayaran}', [\App\Http\Controllers\Admin\PembayaranSppController::class, 'destroy'])->name('pembayaran.destroy');
        Route::post('pembayaran/{pembayaran}/konfirmasi', [\App\Http\Controllers\Admin\PembayaranSppController::class, 'konfirmasi'])->name('pembayaran.konfirmasi');
        Route::get('laporan', [\App\Http\Controllers\Admin\LaporanKeuanganController::class, 'index'])->name('laporan');
        Route::get('tunggakan', [\App\Http\Controllers\Admin\LaporanKeuanganController::class, 'tunggakan'])->name('tunggakan');
        Route::get('kwitansi/{pembayaran}', [\App\Http\Controllers\Admin\LaporanKeuanganController::class, 'kwitansi'])->name('kwitansi');
    });

    // --- CMS Website ---
    Route::prefix('cms')->name('cms.')->group(function () {
        Route::get('/pengaturan', [\App\Http\Controllers\Admin\PengaturanController::class, 'index'])->name('pengaturan');
        Route::put('/pengaturan', [\App\Http\Controllers\Admin\PengaturanController::class, 'update'])->name('pengaturan.update');
        Route::resource('halaman', \App\Http\Controllers\Admin\HalamanStatisController::class)->except(['show'])->names([
            'index'   => 'halaman.index',
            'create'  => 'halaman.create',
            'store'   => 'halaman.store',
            'edit'    => 'halaman.edit',
            'update'  => 'halaman.update',
            'destroy' => 'halaman.destroy',
        ]);
    });

    // --- Fase 13: CMS Artikel & Galeri ---
    Route::prefix('cms')->name('cms.')->group(function () {
        Route::resource('artikel', \App\Http\Controllers\Admin\ArtikelController::class)->except(['show']);
        Route::resource('galeri', \App\Http\Controllers\Admin\GaleriController::class);
        Route::delete('galeri-item/{item}', [\App\Http\Controllers\Admin\GaleriController::class, 'destroyItem'])->name('galeri-item.destroy');
    });

    // --- Fase 12 (tambahan): Struktur Organisasi ---
    Route::prefix('organisasi')->name('organisasi.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\StrukturOrganisasiController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\StrukturOrganisasiController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\StrukturOrganisasiController::class, 'store'])->name('store');
        Route::get('/{organisasi}/edit', [\App\Http\Controllers\Admin\StrukturOrganisasiController::class, 'edit'])->name('edit');
        Route::put('/{organisasi}', [\App\Http\Controllers\Admin\StrukturOrganisasiController::class, 'update'])->name('update');
        Route::delete('/{organisasi}', [\App\Http\Controllers\Admin\StrukturOrganisasiController::class, 'destroy'])->name('destroy');
        Route::get('/{organisasi}/anggota', [\App\Http\Controllers\Admin\StrukturOrganisasiController::class, 'anggota'])->name('anggota');
        Route::post('/{organisasi}/anggota', [\App\Http\Controllers\Admin\StrukturOrganisasiController::class, 'anggotaStore'])->name('anggota.store');
        Route::delete('/{organisasi}/anggota/{anggota}', [\App\Http\Controllers\Admin\StrukturOrganisasiController::class, 'anggotaDestroy'])->name('anggota.destroy');
    });

    // --- Fase 14: PPDB Plus (Gelombang, Persyaratan, FAQ) ---
    Route::prefix('ppdb')->name('ppdb.')->group(function () {
        Route::resource('gelombang', \App\Http\Controllers\Admin\GelombangPpdbController::class)->except(['show'])->names([
            'index' => 'gelombang.index', 'create' => 'gelombang.create', 'store' => 'gelombang.store',
            'edit' => 'gelombang.edit', 'update' => 'gelombang.update', 'destroy' => 'gelombang.destroy',
        ]);
        Route::resource('persyaratan', \App\Http\Controllers\Admin\PersyaratanPpdbController::class)
            ->only(['index', 'store', 'update', 'destroy'])->names([
                'index' => 'persyaratan.index', 'store' => 'persyaratan.store',
                'update' => 'persyaratan.update', 'destroy' => 'persyaratan.destroy',
            ]);
        Route::resource('faq', \App\Http\Controllers\Admin\FaqPpdbController::class)
            ->only(['index', 'store', 'update', 'destroy'])->names([
                'index' => 'faq.index', 'store' => 'faq.store',
                'update' => 'faq.update', 'destroy' => 'faq.destroy',
            ]);
    });

    // --- Fase 9: PPDB (Data Pendaftar) ---
    Route::prefix('ppdb')->name('ppdb.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PpdbController::class, 'index'])->name('index');
        Route::get('/laporan', [\App\Http\Controllers\Admin\PpdbController::class, 'laporan'])->name('laporan');
        Route::get('/export', [\App\Http\Controllers\Admin\PpdbController::class, 'export'])->name('export');
        Route::get('/{pendaftar}', [\App\Http\Controllers\Admin\PpdbController::class, 'show'])->name('show');
        Route::post('/{pendaftar}/verifikasi', [\App\Http\Controllers\Admin\PpdbController::class, 'verifikasi'])->name('verifikasi');
        Route::post('/{pendaftar}/terima', [\App\Http\Controllers\Admin\PpdbController::class, 'terima'])->name('terima');
        Route::post('/{pendaftar}/tolak', [\App\Http\Controllers\Admin\PpdbController::class, 'tolak'])->name('tolak');
    });

    // --- Fase 15: Pesan Kontak ---
    Route::prefix('kontak')->name('kontak.')->group(function () {
        Route::get('/',             [\App\Http\Controllers\Admin\KontakController::class, 'index'])->name('index');
        Route::get('/{kontak}',     [\App\Http\Controllers\Admin\KontakController::class, 'show'])->name('show');
        Route::delete('/{kontak}',  [\App\Http\Controllers\Admin\KontakController::class, 'destroy'])->name('destroy');
        Route::post('/read-all',    [\App\Http\Controllers\Admin\KontakController::class, 'markAllRead'])->name('readAll');
    });
});

Route::middleware(['auth', 'role:2'])->prefix('siswa')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\SiswaController::class, 'dashboard'])->name('siswa.dashboard');
    Route::get('/jadwal', [\App\Http\Controllers\SiswaController::class, 'jadwal'])->name('siswa.jadwal');
    Route::get('/mapel', [\App\Http\Controllers\SiswaController::class, 'mapel'])->name('siswa.mapel');
    Route::get('/absensi', [\App\Http\Controllers\Siswa\AbsensiController::class, 'index'])->name('siswa.absensi');
    Route::get('/nilai', [\App\Http\Controllers\Siswa\NilaiController::class, 'index'])->name('siswa.nilai');
    Route::get('/raport', [\App\Http\Controllers\Siswa\NilaiController::class, 'raport'])->name('siswa.raport');
    Route::get('/pkl', [\App\Http\Controllers\Siswa\PklController::class, 'index'])->name('siswa.pkl');
    Route::get('/pkl/jurnal', [\App\Http\Controllers\Siswa\PklController::class, 'jurnal'])->name('siswa.pkl.jurnal');
    Route::post('/pkl/jurnal', [\App\Http\Controllers\Siswa\PklController::class, 'jurnalStore'])->name('siswa.pkl.jurnal.store');
    Route::get('/pkl/absensi', [\App\Http\Controllers\Siswa\PklController::class, 'absensi'])->name('siswa.pkl.absensi');
    Route::post('/pkl/absensi/masuk', [\App\Http\Controllers\Siswa\PklController::class, 'absensiMasuk'])->name('siswa.pkl.absensi.masuk');
    Route::post('/pkl/absensi/pulang', [\App\Http\Controllers\Siswa\PklController::class, 'absensiPulang'])->name('siswa.pkl.absensi.pulang');
    // --- Fase 6: Kesiswaan ---
    Route::get('/kesiswaan', [\App\Http\Controllers\Siswa\KesiswaanController::class, 'index'])->name('siswa.kesiswaan');
    // --- Fase 7: SPP ---
    Route::get('/spp', [\App\Http\Controllers\Siswa\SppController::class, 'index'])->name('siswa.spp');
    Route::get('/spp/{tagihan}', [\App\Http\Controllers\Siswa\SppController::class, 'show'])->name('siswa.spp.show');
    Route::post('/spp/{tagihan}/upload', [\App\Http\Controllers\Siswa\SppController::class, 'uploadBukti'])->name('siswa.spp.upload');
    Route::post('/spp/{tagihan}/snap-token', [\App\Http\Controllers\Siswa\SppController::class, 'getSnapToken'])->name('siswa.spp.snap-token');

    // --- E-Learning: Materi ---
    Route::get('/materi', [\App\Http\Controllers\Siswa\MateriController::class, 'index'])->name('siswa.materi.index');
    Route::get('/materi/jadwal/{jadwal}', [\App\Http\Controllers\Siswa\MateriController::class, 'showByJadwal'])->name('siswa.materi.jadwal');
    Route::get('/materi/detail/{materi}', [\App\Http\Controllers\Siswa\MateriController::class, 'detail'])->name('siswa.materi.detail');

    // --- E-Learning: Tugas ---
    Route::get('/tugas', [\App\Http\Controllers\Siswa\TugasController::class, 'index'])->name('siswa.tugas.index');
    Route::get('/tugas/{tugas}', [\App\Http\Controllers\Siswa\TugasController::class, 'show'])->name('siswa.tugas.show');
    Route::post('/tugas/{tugas}/kumpul', [\App\Http\Controllers\Siswa\TugasController::class, 'kumpul'])->name('siswa.tugas.kumpul');
    Route::delete('/tugas/kumpulan/{pengumpulan}', [\App\Http\Controllers\Siswa\TugasController::class, 'hapusKumpulan'])->name('siswa.tugas.hapus');
});

Route::middleware(['auth', 'role:1'])->prefix('guru')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\GuruDashboardController::class, 'index'])->name('guru.dashboard');
    Route::get('/jadwal', [\App\Http\Controllers\GuruDashboardController::class, 'jadwal'])->name('guru.jadwal')->middleware('guru.can:menu_jadwal');
    Route::get('/mapel', [\App\Http\Controllers\GuruDashboardController::class, 'mapel'])->name('guru.mapel')->middleware('guru.can:menu_mapel');

    Route::middleware('guru.can:menu_absensi')->group(function () {
        Route::get('/absensi', [\App\Http\Controllers\Guru\AbsensiController::class, 'index'])->name('guru.absensi.index');
        Route::get('/absensi/{jadwal}', [\App\Http\Controllers\Guru\AbsensiController::class, 'form'])->name('guru.absensi.form');
        Route::post('/absensi/{jadwal}', [\App\Http\Controllers\Guru\AbsensiController::class, 'simpan'])->name('guru.absensi.simpan');
        Route::get('/absensi/{jadwal}/rekap', [\App\Http\Controllers\Guru\AbsensiController::class, 'rekap'])->name('guru.absensi.rekap');
    });

    Route::middleware('guru.can:menu_nilai')->group(function () {
        Route::get('/nilai', [\App\Http\Controllers\Guru\NilaiController::class, 'index'])->name('guru.nilai.index');
        Route::get('/nilai/{jadwal}', [\App\Http\Controllers\Guru\NilaiController::class, 'form'])->name('guru.nilai.form');
        Route::post('/nilai/{jadwal}', [\App\Http\Controllers\Guru\NilaiController::class, 'simpan'])->name('guru.nilai.simpan');
        Route::get('/nilai/{jadwal}/rekap', [\App\Http\Controllers\Guru\NilaiController::class, 'rekap'])->name('guru.nilai.rekap');
    });

    Route::middleware('guru.can:menu_pkl')->group(function () {
        Route::get('/pkl', [\App\Http\Controllers\Guru\PklController::class, 'index'])->name('guru.pkl.index');
        Route::get('/pkl/{penempatan}', [\App\Http\Controllers\Guru\PklController::class, 'detail'])->name('guru.pkl.detail');
        Route::get('/pkl/{penempatan}/nilai', [\App\Http\Controllers\Guru\PklController::class, 'nilaiForm'])->name('guru.pkl.nilai');
        Route::post('/pkl/{penempatan}/nilai', [\App\Http\Controllers\Guru\PklController::class, 'nilaiSimpan'])->name('guru.pkl.nilai.simpan');
    });

    // --- Fase 6: Ekskul Bimbingan ---
    Route::middleware('guru.can:menu_ekskul')->group(function () {
        Route::get('/ekskul', [\App\Http\Controllers\Guru\EkskulController::class, 'index'])->name('guru.ekskul.index');
        Route::get('/ekskul/{ekskul}', [\App\Http\Controllers\Guru\EkskulController::class, 'detail'])->name('guru.ekskul.detail');
        Route::get('/ekskul/{ekskul}/absensi', [\App\Http\Controllers\Guru\EkskulController::class, 'absensiForm'])->name('guru.ekskul.absensi');
        Route::post('/ekskul/{ekskul}/absensi', [\App\Http\Controllers\Guru\EkskulController::class, 'absensiStore'])->name('guru.ekskul.absensi.store');
        Route::get('/ekskul/{ekskul}/rekap', [\App\Http\Controllers\Guru\EkskulController::class, 'absensiRekap'])->name('guru.ekskul.rekap');
    });

    // --- E-Learning: Materi ---
    Route::middleware('guru.can:menu_mapel')->group(function () {
        Route::get('/materi', [\App\Http\Controllers\Guru\MateriController::class, 'index'])->name('guru.materi.index');
        Route::get('/materi/{jadwal}/daftar', [\App\Http\Controllers\Guru\MateriController::class, 'show'])->name('guru.materi.show');
        Route::get('/materi/{jadwal}/create', [\App\Http\Controllers\Guru\MateriController::class, 'create'])->name('guru.materi.create');
        Route::post('/materi/{jadwal}', [\App\Http\Controllers\Guru\MateriController::class, 'store'])->name('guru.materi.store');
        Route::get('/materi/edit/{materi}', [\App\Http\Controllers\Guru\MateriController::class, 'edit'])->name('guru.materi.edit');
        Route::put('/materi/edit/{materi}', [\App\Http\Controllers\Guru\MateriController::class, 'update'])->name('guru.materi.update');
        Route::delete('/materi/{materi}', [\App\Http\Controllers\Guru\MateriController::class, 'destroy'])->name('guru.materi.destroy');
    });

    // --- E-Learning: Tugas ---
    Route::middleware('guru.can:menu_nilai')->group(function () {
        Route::get('/tugas', [\App\Http\Controllers\Guru\TugasController::class, 'index'])->name('guru.tugas.index');
        Route::get('/tugas/{jadwal}/daftar', [\App\Http\Controllers\Guru\TugasController::class, 'show'])->name('guru.tugas.show');
        Route::get('/tugas/{jadwal}/create', [\App\Http\Controllers\Guru\TugasController::class, 'create'])->name('guru.tugas.create');
        Route::post('/tugas/{jadwal}', [\App\Http\Controllers\Guru\TugasController::class, 'store'])->name('guru.tugas.store');
        Route::get('/tugas/edit/{tugas}', [\App\Http\Controllers\Guru\TugasController::class, 'edit'])->name('guru.tugas.edit');
        Route::put('/tugas/edit/{tugas}', [\App\Http\Controllers\Guru\TugasController::class, 'update'])->name('guru.tugas.update');
        Route::delete('/tugas/{tugas}', [\App\Http\Controllers\Guru\TugasController::class, 'destroy'])->name('guru.tugas.destroy');
        Route::get('/tugas/{tugas}/pengumpulan', [\App\Http\Controllers\Guru\TugasController::class, 'pengumpulan'])->name('guru.tugas.pengumpulan');
        Route::post('/tugas/nilai/{pengumpulan}', [\App\Http\Controllers\Guru\TugasController::class, 'beriNilai'])->name('guru.tugas.nilai');
    });
});

// --- Fase 8: Pengumuman & Notifikasi (admin CRUD) ---
Route::middleware(['auth', 'role:0'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('pengumuman', \App\Http\Controllers\Admin\PengumumanController::class);
    Route::post('pengumuman/{pengumuman}/publish', [\App\Http\Controllers\Admin\PengumumanController::class, 'publish'])->name('pengumuman.publish');

    // WhatsApp Log
    Route::get('whatsapp/log', [\App\Http\Controllers\Admin\WaLogController::class, 'index'])->name('whatsapp.log');

    // --- Fase B: Akun Orang Tua / Wali ---
    Route::resource('orangtua', \App\Http\Controllers\Admin\OrangTuaController::class)->parameters(['orangtua' => 'orangtua'])->except(['show']);
    Route::post('orangtua/{orangtua}/anak', [\App\Http\Controllers\Admin\OrangTuaController::class, 'assignAnak'])->name('orangtua.anak.assign');
    Route::delete('orangtua/{orangtua}/anak/{siswa}', [\App\Http\Controllers\Admin\OrangTuaController::class, 'detachAnak'])->name('orangtua.anak.detach');

    // --- Fase E: Audit Log ---
    Route::get('audit-log', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-log.index');
    Route::get('audit-log/export', [\App\Http\Controllers\Admin\AuditLogController::class, 'export'])->name('audit-log.export');

    // --- Fase F: Laporan ---
    Route::get('laporan', [\App\Http\Controllers\Admin\LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/kehadiran', [\App\Http\Controllers\Admin\LaporanController::class, 'kehadiran'])->name('laporan.kehadiran');
    Route::get('laporan/nilai', [\App\Http\Controllers\Admin\LaporanController::class, 'nilai'])->name('laporan.nilai');
    Route::get('laporan/keuangan', [\App\Http\Controllers\Admin\LaporanController::class, 'keuangan'])->name('laporan.keuangan');
    Route::get('laporan/ppdb', [\App\Http\Controllers\Admin\LaporanController::class, 'ppdb'])->name('laporan.ppdb');

    // Maintenance mode toggle (admin only)
    Route::get('maintenance/status', [\App\Http\Controllers\Admin\MaintenanceController::class, 'status'])->name('maintenance.status');
    Route::post('maintenance/enable', [\App\Http\Controllers\Admin\MaintenanceController::class, 'enable'])->name('maintenance.enable');
    Route::post('maintenance/disable', [\App\Http\Controllers\Admin\MaintenanceController::class, 'disable'])->name('maintenance.disable');
});

// --- Fase B: Portal Orang Tua / Wali (role=4) ---
Route::middleware(['auth', 'role:4'])->prefix('orangtua')->name('orangtua.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\OrangTua\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/anak/switch', [\App\Http\Controllers\OrangTua\AnakController::class, 'switch'])->name('anak.switch');
    Route::get('/nilai', [\App\Http\Controllers\OrangTua\NilaiController::class, 'index'])->name('nilai');
    Route::get('/absensi', [\App\Http\Controllers\OrangTua\AbsensiController::class, 'index'])->name('absensi');
    Route::get('/kesiswaan', [\App\Http\Controllers\OrangTua\KesiswaanController::class, 'index'])->name('kesiswaan');
    Route::get('/spp', [\App\Http\Controllers\OrangTua\SppController::class, 'index'])->name('spp');
    Route::get('/spp/{tagihan}', [\App\Http\Controllers\OrangTua\SppController::class, 'show'])->name('spp.show');
    Route::post('/spp/{tagihan}/snap-token', [\App\Http\Controllers\OrangTua\SppController::class, 'getSnapToken'])->name('spp.snap-token');
});

// --- Fase 8: Pengumuman & Notifikasi (semua role) ---
Route::middleware('auth')->group(function () {
    Route::get('/pengumuman', [\App\Http\Controllers\PengumumanController::class, 'index'])->name('pengumuman.index');
    Route::get('/pengumuman/{pengumuman}', [\App\Http\Controllers\PengumumanController::class, 'show'])->name('pengumuman.show');

    Route::get('/notifikasi', [\App\Http\Controllers\NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/{notifikasi}/read', [\App\Http\Controllers\NotifikasiController::class, 'markRead'])->name('notifikasi.read');
    Route::post('/notifikasi/read-all', [\App\Http\Controllers\NotifikasiController::class, 'markAllRead'])->name('notifikasi.readAll');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Autentikasi Dua Faktor (2FA)
    Route::get('/profile/2fa', [\App\Http\Controllers\TwoFactorController::class, 'show'])->name('profile.2fa');
    Route::post('/profile/2fa/enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('profile.2fa.confirm');
    Route::delete('/profile/2fa', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('profile.2fa.disable');
});

require __DIR__.'/auth.php';
