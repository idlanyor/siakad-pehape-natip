# Panduan Instalasi — SIAKAD SMK

Stack: **Laravel 11 · PHP 8.2+ · MySQL · AdminLTE 3**

## Persyaratan Server

- PHP >= 8.2 dengan ekstensi: `pdo`, `mbstring`, `openssl`, `tokenizer`, `curl`, `gd`/`imagick`
- MySQL 5.7+ / MariaDB 10.3+
- Composer 2.x
- Node.js 18+ & NPM (untuk build aset frontend)
- Web server: Nginx / Apache

## A. Instalasi via Wizard (Direkomendasikan)

1. Upload/clone kode ke server, arahkan document root ke folder `public/`.
2. Install dependency:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```
3. Siapkan file environment & app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Buat database kosong di MySQL (mis. `siakad`).
5. Buka browser ke `https://domain-anda.com/install`.
6. Ikuti wizard: cek persyaratan → konfigurasi database → identitas sekolah & akun admin.
7. Selesai. Wizard otomatis terkunci (file penanda `storage/installed`).

> **Catatan keamanan:** Wizard hanya bisa diakses jika `storage/installed` belum ada.
> Untuk install ulang dari nol, hapus file tersebut.

## B. Instalasi Manual (CLI)

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# Edit .env: DB_*, APP_URL, APP_NAME
php artisan migrate --seed
npm install && npm run build
# Buat penanda agar wizard /install terkunci
echo "manual install" > storage/installed
```

Buat akun admin pertama via tinker:
```bash
php artisan tinker
>>> \App\Models\User::create(['name'=>'Admin','username'=>'admin','email'=>'admin@sekolah.id','password'=>bcrypt('rahasia'),'role'=>0]);
```

## C. Konfigurasi Tambahan (.env)

| Grup | Variabel |
|------|----------|
| Midtrans (SPP online) | `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION` |
| WhatsApp Gateway | `WA_API_URL`, `WA_API_KEY`, `WA_NOTIFICATION_ENABLED` |

## D. Daemon Wajib (Produksi)

Queue worker (kirim WhatsApp async) & scheduler (reminder SPP, backup harian):

```bash
# Supervisor / systemd
php artisan queue:work --tries=3

# Crontab (jalankan scheduler tiap menit)
* * * * * cd /path/ke/proyek && php artisan schedule:run >> /dev/null 2>&1
```

Scheduler sudah mencakup: reminder SPP harian 08:00, backup database harian 02:00,
pembersihan backup lama tiap minggu.

## E. Keamanan Produksi

- Set `APP_ENV=production` & `APP_DEBUG=false` di `.env`.
- HTTPS otomatis dipaksa saat `APP_ENV=production`.
- Aktifkan 2FA untuk akun admin via menu profil (ikon perisai di navbar).
- Backup tersimpan di `storage/app/` (spatie/laravel-backup). Konfigurasi tujuan di `config/backup.php`.

## F. Optimasi (setelah deploy / update)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
