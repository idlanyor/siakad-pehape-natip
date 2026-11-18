# SIAKAD (Sistem Informasi Akademik)

Sistem Informasi Akademik berbasis web yang dikembangkan menggunakan framework Laravel. Sistem ini dirancang untuk mengelola berbagai proses akademik sekolah mulai dari Penerimaan Peserta Didik Baru (PPDB), manajemen pembelajaran, hingga administrasi keuangan (SPP).

## 🌟 Fitur Utama

Aplikasi ini memiliki 3 hak akses utama: **Admin**, **Guru**, dan **Siswa**.

### 1. Modul Admin
* **Dashboard & Statistik**: Ringkasan data akademik dan keuangan.
* **Manajemen Pengguna**: Kelola data Admin, Guru, dan Siswa.
* **Manajemen Master Data**: Kelola Jurusan, Mata Pelajaran (Mapel), dan Kelas.
* **PPDB (Penerimaan Peserta Didik Baru)**: Kelola pendaftaran, seleksi, dan pengumuman siswa baru.
* **Keuangan**: Manajemen tagihan SPP dan pencatatan pembayaran SPP.
* **Integrasi WhatsApp**: Log notifikasi WA (Pesan gateway untuk tagihan/pengumuman).
* **Pengumuman**: Broadcast informasi ke guru dan siswa.

### 2. Modul Guru
* **Manajemen Kelas & Mapel**: Mengelola mata pelajaran yang diampu.
* **Materi & Tugas**: Upload materi pelajaran dan memberikan tugas ke siswa.
* **Absensi**: Input dan rekap kehadiran siswa.
* **Penilaian**: Manajemen nilai tugas dan evaluasi.

### 3. Modul Siswa
* **Dashboard Akademik**: Lihat jadwal dan informasi sekolah.
* **Akses Pembelajaran**: Download materi dan kumpul tugas.
* **Informasi Keuangan**: Cek riwayat pembayaran dan tagihan SPP aktif.
* **Absensi**: Cek rekap kehadiran mandiri.

### 4. Ujian Online (CBT)
Modul di `app/Modules/Ujian`, diakses di `/ujian` dengan login SIAKAD yang sama.
* **Admin**: Periode & fase ujian (paksa buka/tutup), semua ujian, penugasan guru pengawas.
* **Guru**: Bank soal per mapel, susun soal ujian (manual / dari bank), terbitkan, koreksi essay, export hasil Excel/PDF.
* **Pengawas (proktor)**: Monitor peserta (online, progres, sisa waktu, pelanggaran), paksa selesai, diskualifikasi, reset sesi.
* **Siswa**: Mengerjakan dengan timer, autosave, tanda ragu-ragu, soal/opsi teracak per siswa, deteksi pindah tab & copy-paste.
  Halaman depan `/` untuk tamu adalah login siswa dengan NIS.

Konfigurasi opsional di `.env`: `UJIAN_BATAS_PELANGGARAN` (default 5; `0` = tanpa diskualifikasi otomatis).

## 💻 Tech Stack

* **Backend**: Laravel (PHP)
* **Frontend**: Blade Templating, Tailwind CSS / Bootstrap (AdminLTE); modul Ujian Online memakai Inertia + Vue 3
* **Database**: MySQL
* **Package Manager**: Composer & NPM

## 📋 Persyaratan Sistem

Sebelum menjalankan aplikasi, pastikan sistem Anda telah terinstal:
* PHP >= 8.1
* Composer >= 2.0
* Node.js & NPM
* MySQL / MariaDB
* Ekstensi PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## 🚀 Panduan Instalasi

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di *local environment*:

1. **Clone repositori**
   ```bash
   git clone <repo-url>
   cd siakad-pehape-natip
   ```

2. **Install dependensi PHP dan Node.js**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**
   Salin file `.env.example` menjadi `.env`.
   ```bash
   cp .env.example .env
   ```
   Sesuaikan konfigurasi database pada file `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database_anda
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Migrasi Database dan Seeding**
   ```bash
   php artisan migrate --seed
   ```

6. **Compile Aset Frontend**
   ```bash
   npm run build
   # atau gunakan 'npm run dev' untuk development
   ```

7. **Jalankan Local Server**
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui `http://localhost:8000`.

## 📁 Struktur Dokumen Penting

Selain `README.md`, perhatikan juga dokumen manajemen proyek berikut:
* `ROADMAP.md` - Peta jalan pengembangan fitur jangka panjang.
* `PROGRESS.md` - Laporan progres pengerjaan harian/mingguan.
* `Sprint.md` - Catatan task management dan sprint saat ini.

## 🔒 Keamanan & Perhatian

* Pastikan file `.env` tidak pernah di-commit ke repositori publik.
* Ubah kredensial default dari seeder segera setelah aplikasi di-deploy ke production.
