# SIAKAD — Sistem Informasi Akademik Sekolah

Aplikasi web open source untuk mengelola sekolah (khususnya SMK) dalam satu tempat: website sekolah, PPDB online, akademik, keuangan SPP, kesiswaan, PKL, hingga **ujian online (CBT)**. Dibangun dengan Laravel 12.

[![Tests](https://github.com/idlanyor/siakad-pehape-natip/actions/workflows/tests.yml/badge.svg)](https://github.com/idlanyor/siakad-pehape-natip/actions/workflows/tests.yml)
[![Lisensi: MIT](https://img.shields.io/badge/lisensi-MIT-blue.svg)](LICENSE)

## Fitur

**Website publik**
- Beranda, profil sekolah, program keahlian, struktur organisasi, berita, galeri, prestasi — semua dikelola dari CMS.
- Portal **PPDB**: jadwal gelombang & kuota, formulir online, cek status, cetak bukti pendaftaran.

**Peran pengguna**

| Peran | Yang bisa dilakukan |
|---|---|
| **Admin** | Data master (tahun ajaran, jurusan, kelas, mapel, guru, siswa), jadwal, impor Excel, PPDB, SPP & laporan keuangan, kesiswaan (ekskul, pelanggaran, surat keterangan), PKL, rapor, CMS, pengumuman, audit log, mode maintenance, backup |
| **Guru** | Absensi, materi & tugas, penilaian, ekskul binaan, pembimbing PKL, ujian online |
| **Siswa** | Jadwal, materi, tugas, nilai, absensi, tagihan SPP, jurnal PKL, ujian online |
| **Orang tua** | Pantau absensi, nilai, dan tagihan SPP anak |
| **Pendaftar PPDB** | Unggah berkas dan pantau status pendaftaran |

**Ujian Online (CBT)** — modul `app/Modules/Ujian`, di `/ujian`
- Guru: bank soal per mapel, soal pilihan ganda & essay (format teks dan gambar), acak soal/opsi per siswa, koreksi essay, ekspor Excel/PDF.
- Pengawas: pantau peserta secara langsung, tambah waktu, kirim pengumuman, paksa selesai, diskualifikasi, reset sesi.
- Siswa: masuk dengan NIS di `/masuk-ujian`, timer mengikuti server, jawaban tersimpan otomatis (tahan putus koneksi), deteksi pindah tab, satu perangkat per sesi.

**Lainnya**: verifikasi dua langkah (2FA), notifikasi WhatsApp, pembayaran SPP via Midtrans, API untuk aplikasi mobile (Laravel Sanctum).

## Teknologi

- **Backend**: Laravel 12, PHP 8.4
- **Frontend**: Blade + Bootstrap/AdminLTE; modul Ujian memakai Inertia + Vue 3 (Vite)
- **Database**: MySQL/MariaDB (produksi) atau SQLite (coba cepat & test)

## Persyaratan

- PHP **8.4** atau lebih baru, dengan ekstensi: bcmath, ctype, fileinfo, gd, intl, mbstring, openssl, pdo (mysql/sqlite), tokenizer, xml, zip
- Composer 2
- Node.js 20+ dan npm
- MySQL 8 / MariaDB 10.6+ (atau SQLite)

## Instalasi

```bash
git clone https://github.com/idlanyor/siakad-pehape-natip.git siakad
cd siakad

composer install
npm ci && npm run build

cp .env.example .env
php artisan key:generate
php artisan storage:link
```

Lalu pilih salah satu:

### A. Untuk sekolah (produksi)

1. Atur database di `.env` (`DB_CONNECTION=mysql`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), serta `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL`.
2. Buka `https://domain-sekolah/install` dan ikuti installer: tes koneksi database, migrasi, lalu buat akun admin dengan password pilihan sendiri.
3. Masuk sebagai admin, lengkapi **Pengaturan** (nama sekolah, logo, alamat, kontak) — data ini dipakai di website, rapor, surat, dan bukti PPDB.
4. Jalankan scheduler dan queue worker di server:
   ```cron
   * * * * * cd /path/ke/siakad && php artisan schedule:run >> /dev/null 2>&1
   ```
   ```bash
   php artisan queue:work --tries=3
   ```
   Scheduler menjalankan pengingat SPP (08.00) dan backup harian (02.00).

### B. Untuk mencoba / pengembangan (dengan data demo)

```bash
touch database/database.sqlite   # .env bawaan memakai SQLite
php artisan migrate --seed
php artisan serve
```

Buka `http://localhost:8000`. Data demo membuat akun seperti `admin` / `password`.
**Jangan menjalankan seeder demo di server produksi** — `db:seed` otomatis melewati akun demo bila `APP_ENV=production`.

## Konfigurasi opsional (`.env`)

| Variabel | Fungsi |
|---|---|
| `WHATSAPP_ENABLED`, `WHATSAPP_API_URL`, `WHATSAPP_API_KEY` | Notifikasi & pengingat SPP lewat gateway WhatsApp |
| `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION` | Pembayaran SPP online |
| `MAIL_*` | Email (lupa password, notifikasi) |
| `UJIAN_BATAS_PELANGGARAN` | Jumlah pelanggaran sebelum siswa didiskualifikasi otomatis (default 5, `0` = nonaktif) |

## Pembaruan

```bash
git pull
composer install --no-dev -o
npm ci && npm run build
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
```

## Pengembangan

```bash
php artisan test         # seluruh test (SQLite in-memory)
vendor/bin/pint --dirty  # format file PHP yang diubah
npm run dev              # Vite dev server
```

Test `SmokeSemuaHalamanTest` membuka setiap halaman untuk setiap peran dan gagal bila ada error 500 — jalankan sebelum mengirim PR.

> Jika config/route sudah di-cache (`php artisan optimize`), bersihkan dulu dengan `php artisan optimize:clear` sebelum menjalankan test.

## Kontribusi

Kontribusi sangat diterima — lihat [CONTRIBUTING.md](CONTRIBUTING.md). Menemukan celah keamanan? Laporkan secara privat sesuai [SECURITY.md](SECURITY.md), jangan lewat issue publik.

## Lisensi

[MIT](LICENSE) © 2026 idlanyor
