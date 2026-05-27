# SIAKAD — Roadmap Development

> Pedoman eksekusi pengembangan fitur lanjutan.
> Dikerjakan bertahap sesuai urutan fase.

---

## Status Saat Ini

Fitur inti sudah selesai (Phase 1):
- [x] Manajemen Akademik (Jadwal, Nilai, Absensi, Raport PDF)
- [x] PKL / Prakerin (Penempatan, Jurnal, Absensi, Nilai)
- [x] Kesiswaan (Pelanggaran, Prestasi, Ekskul, Surat Keterangan)
- [x] SPP / Keuangan (Tarif, Tagihan, Pembayaran Manual, Laporan)
- [x] PPDB (Gelombang, Pendaftaran Online, Seleksi, Akun Otomatis)
- [x] CMS Website (Artikel, Galeri, Halaman Statis, Pengaturan)
- [x] Pengumuman & Notifikasi In-App
- [x] Role-based Access (Admin, Guru, Siswa, Pendaftar)
- [x] Export Excel & PDF

---

## Fase A — Payment Gateway SPP

**Tujuan**: Siswa/orangtua bayar SPP otomatis via Midtrans, konfirmasi tanpa manual.

**Dependencies**: Akun Midtrans (sandbox dulu), `composer require midtrans/midtrans-php`

### Checklist

- [x] Install package Midtrans & konfigurasi `.env`
  ```
  MIDTRANS_SERVER_KEY=
  MIDTRANS_CLIENT_KEY=
  MIDTRANS_IS_PRODUCTION=false
  ```
- [x] Buat `MidtransService` di `app/Services/MidtransService.php`
  - Method `createSnapToken(TagihanSpp $tagihan): array`
  - Method `verifySignature(...)`: bool`
- [x] Update controller `Siswa/SppController`:
  - Method `getSnapToken(TagihanSpp $tagihan)` — generate snap token, return JSON
  - Tambah tombol "Bayar Online Sekarang" di halaman detail tagihan
- [x] Buat `MidtransWebhookController`:
  - Verifikasi signature dari Midtrans
  - Update status `TagihanSpp` & `PembayaranSpp` otomatis
  - Kirim notifikasi in-app ke siswa & admin
- [x] Tambah route webhook di `routes/web.php` (bypass CSRF)
- [x] Update migration `pembayaran_spps` — tambah `midtrans_order_id`, `midtrans_transaction_id`, `payment_type`
- [x] Buat view Midtrans Snap di halaman SPP siswa (embed Snap.js + fetch token)
- [ ] **TODO**: Test end-to-end di sandbox Midtrans (isi MIDTRANS_SERVER_KEY & CLIENT_KEY di .env)
- [ ] **TODO**: Update kwitansi PDF untuk menyertakan metode pembayaran digital

**File yang dibuat/diubah:**
- `app/Services/MidtransService.php` (baru)
- `app/Http/Controllers/MidtransWebhookController.php` (baru)
- `app/Http/Controllers/Siswa/SppController.php` (update)
- `resources/views/siswa/spp/show.blade.php` (update)
- `database/migrations/xxxx_add_midtrans_fields_to_pembayaran_spps_table.php` (baru)
- `routes/web.php` (update)
- `.env` / `.env.example` (update)

---

## Fase B — Role Orangtua / Wali Murid

**Tujuan**: Orangtua punya akun sendiri, bisa pantau anak & bayar SPP.

**Dependencies**: Fase A harus selesai (bayar SPP)

### Checklist

- [ ] Tambah `role = 4` di dokumentasi dan middleware `RoleMiddleware`
- [ ] Buat migration `create_orang_tuas_table`:
  - `id`, `user_id` (FK), `nama`, `telepon`, `pekerjaan`, `alamat`, `timestamps`
- [ ] Buat migration `create_orang_tua_siswa_table` (pivot):
  - `orang_tua_id`, `siswa_id`, `hubungan` (ayah/ibu/wali)
- [ ] Buat model `OrangTua` dengan relasi `belongsToMany(Siswa::class)`
- [ ] Update model `Siswa` — tambah relasi `orangTua()`
- [ ] Update model `User` — tambah `isOrangTua()` method & relasi `orangTua()`
- [ ] Update `Admin/SiswaAdminController` — tambah form tambah/edit orangtua di halaman siswa
- [ ] Buat controller `OrangTuaController` (untuk akses admin):
  - CRUD akun orangtua
  - Assign orangtua ke siswa
- [ ] Buat controllers untuk dashboard orangtua di `app/Http/Controllers/OrangTua/`:
  - `DashboardController` — pilih anak aktif, ringkasan
  - `AnakController` — switch aktif anak (jika punya >1)
  - `NilaiController` — lihat nilai anak
  - `AbsensiController` — lihat absensi anak
  - `KesiswaanController` — lihat pelanggaran & prestasi anak
  - `SppController` — lihat tagihan & bayar (gunakan Midtrans dari Fase A)
- [ ] Buat views di `resources/views/orangtua/`
- [ ] Tambah routes di `routes/web.php`:
  ```php
  Route::middleware(['auth', 'role:4'])->prefix('orangtua')->name('orangtua.')->group(...)
  ```
- [ ] Update redirect dashboard utama untuk `role = 4`
- [ ] Notifikasi ke orangtua saat:
  - Siswa tidak hadir (alpa)
  - Nilai baru diinput
  - Tagihan SPP baru dibuat
  - Pembayaran berhasil

**File yang dibuat/diubah:**
- `database/migrations/xxxx_create_orang_tuas_table.php` (baru)
- `database/migrations/xxxx_create_orang_tua_siswa_table.php` (baru)
- `app/Models/OrangTua.php` (baru)
- `app/Http/Controllers/OrangTua/` (baru, semua controller)
- `app/Http/Controllers/Admin/OrangTuaController.php` (baru)
- `resources/views/orangtua/` (baru, semua views)
- `routes/web.php` (update)

---

## Fase C — WhatsApp Notification

**Tujuan**: Kirim notifikasi penting otomatis via WhatsApp.

**Dependencies**: Akun provider WA (rekomendasi: **Fonnte** karena murah & mudah)

### Checklist

- [ ] Daftar akun Fonnte (fonnte.com), dapatkan API token
- [ ] Tambah konfigurasi `.env`:
  ```
  FONNTE_TOKEN=
  FONNTE_SENDER=   # nomor WA pengirim
  WA_NOTIFICATION_ENABLED=true
  ```
- [ ] Buat `WhatsAppService` di `app/Services/WhatsAppService.php`:
  - Method `send(string $phone, string $message): bool`
  - Handle error & logging
- [ ] Tambah kolom `no_wa` (nullable) ke tabel:
  - `siswas` (migration baru)
  - `gurus` (migration baru)
  - `orang_tuas` (sudah ada `telepon`, tambah `no_wa` atau gunakan telepon)
- [ ] Buat template pesan di `app/Services/WaTemplates.php`:
  - `sppJatuhTempo(Siswa $siswa, TagihanSpp $tagihan): string`
  - `sppBerhasil(Siswa $siswa, PembayaranSpp $pembayaran): string`
  - `nilaiBaruDiinput(Siswa $siswa, Jadwal $jadwal): string`
  - `siswaAlpa(Siswa $siswa, Absensi $absensi): string`
  - `pengumumanBaru(string $judul, string $ringkasan): string`
  - `ppdpDiterima(PendaftarPpdb $pendaftar): string`
- [ ] Buat Jobs (Queue) untuk setiap notifikasi:
  - `SendWaNotification` job di `app/Jobs/`
  - Dispatch job async agar tidak blocking response
- [ ] Trigger notifikasi WA di:
  - `Admin/PembayaranSppController@konfirmasi` → kirim WA ke siswa & orangtua
  - `Guru/AbsensiController@simpan` → kirim WA ke orangtua jika alpa
  - `Admin/TagihanSppController@store` (bulk create) → kirim WA tagihan baru
  - `Admin/PengumumanController@publish` → kirim WA ke semua target
  - `Admin/PpdbController@terima` → kirim WA ke pendaftar
- [ ] Buat halaman log WA di admin (`admin/whatsapp/log`) — lihat history kirim
- [ ] Tambah toggle on/off notifikasi WA per event di pengaturan admin
- [ ] Setup queue worker: `php artisan queue:work`
- [ ] Setup scheduler untuk reminder SPP jatuh tempo (H-3, H-1):
  ```php
  // app/Console/Commands/KirimReminderSpp.php
  Schedule::command('reminder:spp')->dailyAt('08:00');
  ```

**File yang dibuat/diubah:**
- `app/Services/WhatsAppService.php` (baru)
- `app/Services/WaTemplates.php` (baru)
- `app/Jobs/SendWaNotification.php` (baru)
- `app/Console/Commands/KirimReminderSpp.php` (baru)
- `database/migrations/xxxx_add_no_wa_to_siswas_table.php` (baru)
- `database/migrations/xxxx_add_no_wa_to_gurus_table.php` (baru)
- Controller yang mengirim notifikasi (update)

---

## Fase D — REST API + Laravel Sanctum

**Tujuan**: API lengkap untuk mobile app & integrasi pihak ketiga.

**Dependencies**: Tidak ada, bisa paralel dengan fase lain

### Checklist

- [ ] Install Laravel Sanctum: `composer require laravel/sanctum`
- [ ] Publish & run migration Sanctum
- [ ] Setup API routes di `routes/api.php` dengan prefix `/api/v1/`
- [ ] Buat middleware `ForceJsonResponse` untuk semua API routes
- [ ] **Auth API** (`app/Http/Controllers/Api/AuthController.php`):
  - `POST /api/v1/login` — return token
  - `POST /api/v1/logout` — revoke token
  - `GET  /api/v1/me` — profil user login
  - `PUT  /api/v1/me` — update profil
  - `PUT  /api/v1/me/password` — ganti password
- [ ] **API Resources** di `app/Http/Resources/`:
  - `SiswaResource`, `GuruResource`, `JadwalResource`
  - `NilaiResource`, `AbsensiResource`, `TagihanSppResource`
  - `PengumumanResource`, `NotifikasiResource`
- [ ] **Siswa API** (`app/Http/Controllers/Api/Siswa/`):
  - `GET /jadwal` — jadwal aktif
  - `GET /nilai` — nilai per semester
  - `GET /absensi` — rekap absensi
  - `GET /raport` — data raport
  - `GET /pkl` — data PKL & jurnal
  - `POST /pkl/jurnal` — submit jurnal harian
  - `POST /pkl/absensi/masuk` — check-in PKL
  - `POST /pkl/absensi/pulang` — check-out PKL
  - `GET /kesiswaan` — pelanggaran, prestasi, ekskul
  - `GET /spp` — daftar tagihan
  - `GET /spp/{id}` — detail tagihan + payment token Midtrans
  - `GET /pengumuman` — daftar pengumuman
  - `GET /notifikasi` — daftar notifikasi
  - `POST /notifikasi/{id}/read` — tandai baca
- [ ] **Guru API** (`app/Http/Controllers/Api/Guru/`):
  - `GET /jadwal` — jadwal mengajar
  - `GET /absensi` — list kelas untuk absensi
  - `GET /absensi/{jadwal}` — form absensi hari ini
  - `POST /absensi/{jadwal}` — submit absensi
  - `GET /nilai/{jadwal}` — form input nilai
  - `POST /nilai/{jadwal}` — submit nilai
  - `GET /pkl` — list penempatan PKL bimbingan
- [ ] **Orangtua API** (`app/Http/Controllers/Api/OrangTua/`):
  - `GET /anak` — daftar anak
  - `GET /anak/{id}/nilai` — nilai anak
  - `GET /anak/{id}/absensi` — absensi anak
  - `GET /anak/{id}/spp` — tagihan SPP anak
  - `GET /pengumuman` — pengumuman sekolah
- [ ] Rate limiting: 60 req/menit untuk auth, 120 req/menit untuk data
- [ ] Buat Postman Collection (export JSON) di `docs/api/`
- [ ] Buat `API.md` dokumentasi endpoint

**File yang dibuat/diubah:**
- `routes/api.php` (update besar)
- `app/Http/Controllers/Api/` (semua controller baru)
- `app/Http/Resources/` (semua resource baru)
- `app/Http/Middleware/ForceJsonResponse.php` (baru)
- `docs/api/postman_collection.json` (baru)
- `docs/API.md` (baru)

---

## Fase E — 2FA & Audit Log

**Tujuan**: Keamanan berlapis untuk akun admin & pencatatan semua perubahan data.

**Dependencies**: Tidak ada

### Checklist

#### Two-Factor Authentication (2FA)

- [ ] Install package: `composer require pragmarx/google2fa-laravel`
- [ ] Buat migration tambah kolom ke `users`:
  - `two_factor_secret` (text, nullable, encrypted)
  - `two_factor_enabled` (boolean, default false)
  - `two_factor_confirmed_at` (timestamp, nullable)
- [ ] Buat `TwoFactorController`:
  - `GET  /profile/2fa` — halaman setup 2FA
  - `POST /profile/2fa/enable` — generate secret & tampilkan QR
  - `POST /profile/2fa/confirm` — verifikasi kode OTP & aktifkan
  - `POST /profile/2fa/disable` — nonaktifkan 2FA
- [ ] Buat middleware `RequireTwoFactor`:
  - Jika admin (role=0) belum setup 2FA → redirect ke halaman setup
  - Enforce 2FA wajib untuk admin
- [ ] Tambah step verifikasi OTP saat login (setelah email+password sukses)
- [ ] Tambah recovery codes (opsional) untuk backup akses
- [ ] Update halaman profil — tampilkan status & tombol aktifkan/nonaktifkan 2FA

#### Audit Log

- [ ] Buat migration `create_audit_logs_table`:
  - `id`, `user_id` (nullable), `user_name` (string)
  - `action` (string) — created/updated/deleted
  - `model` (string) — nama model
  - `model_id` (string)
  - `old_values` (json, nullable)
  - `new_values` (json, nullable)
  - `ip_address` (string, nullable)
  - `user_agent` (string, nullable)
  - `timestamps`
- [ ] Buat model `AuditLog`
- [ ] Buat trait `Auditable` — dipasang di model-model penting:
  ```php
  // Pasang di: Siswa, Guru, Nilai, PembayaranSpp, TagihanSpp, PendaftarPpdb, User
  use Auditable;
  protected array $auditExclude = ['updated_at'];
  ```
- [ ] Buat observer `AuditObserver` — tangkap event `created`, `updated`, `deleted`
- [ ] Register observer di `AppServiceProvider`
- [ ] Buat halaman audit log di admin (`admin/audit-log`):
  - Filter by: user, model, action, tanggal
  - Tampilkan old vs new values
  - Export ke Excel
- [ ] Tambah brute force protection — lockout 5 menit setelah 5x gagal login

**File yang dibuat/diubah:**
- `app/Http/Controllers/TwoFactorController.php` (baru)
- `app/Http/Middleware/RequireTwoFactor.php` (baru)
- `app/Http/Controllers/Admin/AuditLogController.php` (baru)
- `app/Models/AuditLog.php` (baru)
- `app/Traits/Auditable.php` (baru)
- `app/Observers/AuditObserver.php` (baru)
- `database/migrations/xxxx_add_2fa_to_users_table.php` (baru)
- `database/migrations/xxxx_create_audit_logs_table.php` (baru)
- `resources/views/profile/` (update)
- `resources/views/admin/audit-log/` (baru)

---

## Fase F — Dashboard Analytics

**Tujuan**: Visualisasi data untuk kepala sekolah & admin dengan chart interaktif.

**Dependencies**: Tidak ada (tapi lebih kaya data jika Fase A–E sudah ada)

### Checklist

- [ ] Install ApexCharts via CDN atau npm
- [ ] Refactor `AdminController@dashboard` — pisahkan query ke `DashboardService`
- [ ] Buat `app/Services/DashboardService.php`:
  - `getStatistikKehadiran(int $bulan, int $tahun): array`
  - `getDistribusiNilai(int $kelasId, int $semesterId): array`
  - `getStatusSpp(int $tahunAjaranId): array`
  - `getTrendPpdb(): array`
  - `getTopPrestasi(int $limit = 5): Collection`
  - `getTopPelanggaran(int $limit = 5): Collection`
- [ ] Update halaman dashboard admin — tambah section charts:
  - **Card stats**: total siswa, guru, kelas, pendapatan SPP bulan ini
  - **Line chart**: Tren kehadiran 6 bulan terakhir
  - **Bar chart**: Distribusi nilai per kelas (filter by kelas/semester)
  - **Donut chart**: Status SPP (lunas/cicil/belum bayar/tunggakan)
  - **Bar chart**: Statistik PPDB per gelombang
  - **Table**: 5 siswa dengan poin pelanggaran tertinggi
  - **Table**: 5 siswa berprestasi terbaru
- [ ] Buat halaman **Laporan** khusus (`admin/laporan`):
  - Rekap kehadiran per kelas/semester → export PDF
  - Rekap nilai per kelas/jurusan → export PDF & Excel
  - Laporan keuangan SPP per bulan/tahun → export PDF
  - Laporan PPDB per gelombang → export PDF
- [ ] Filter interaktif: tahun ajaran, semester, kelas, jurusan
- [ ] Print/export chart sebagai gambar (ApexCharts built-in)

**File yang dibuat/diubah:**
- `app/Services/DashboardService.php` (baru)
- `app/Http/Controllers/AdminController.php` (update)
- `app/Http/Controllers/Admin/LaporanController.php` (baru atau update existing)
- `resources/views/admin/dashboard.blade.php` (update besar)
- `resources/views/admin/laporan/` (baru)

---

## Fase G — E-Learning Sederhana

**Tujuan**: Guru upload materi & beri tugas online, terintegrasi dengan sistem nilai.

**Dependencies**: Fase D (API) jika ingin diakses dari mobile

### Checklist

- [ ] Buat migrations:
  - `create_materis_table`: `id`, `jadwal_id`, `guru_id`, `judul`, `deskripsi`, `tipe` (file/link/text), `konten` (url/path), `tanggal_publish`, `timestamps`
  - `create_tugas_table`: `id`, `jadwal_id`, `guru_id`, `judul`, `instruksi`, `deadline`, `nilai_maksimal`, `timestamps`
  - `create_pengumpulan_tugas_table`: `id`, `tugas_id`, `siswa_id`, `file_path`, `catatan`, `nilai`, `dinilai_at`, `timestamps`
- [ ] Buat models: `Materi`, `Tugas`, `PengumpulanTugas`
- [ ] **Guru controllers** (update `Guru/` atau buat baru):
  - CRUD Materi per jadwal
  - CRUD Tugas per jadwal + set deadline
  - Lihat & nilai pengumpulan tugas siswa
- [ ] **Siswa controllers** (baru):
  - `GET /siswa/materi` — list materi per mata pelajaran
  - `GET /siswa/tugas` — daftar tugas + status (belum/sudah kumpul/dinilai)
  - `POST /siswa/tugas/{id}/kumpul` — upload pengumpulan
- [ ] Tambah ke menu guru dan menu siswa
- [ ] Notifikasi in-app (+ WA jika Fase C sudah ada) saat:
  - Guru publish materi baru → notif ke semua siswa di kelas
  - Guru buat tugas baru → notif ke semua siswa + reminder H-1 deadline
  - Guru memberi nilai tugas → notif ke siswa
- [ ] Opsi: integrasikan nilai tugas ke komponen nilai `Nilai` model (kolom `nilai_tugas`)
- [ ] Tambah API endpoints di Fase D untuk materi & tugas

**File yang dibuat/diubah:**
- `database/migrations/xxxx_create_materis_table.php` (baru)
- `database/migrations/xxxx_create_tugas_table.php` (baru)
- `database/migrations/xxxx_create_pengumpulan_tugas_table.php` (baru)
- `app/Models/Materi.php`, `Tugas.php`, `PengumpulanTugas.php` (baru)
- `app/Http/Controllers/Guru/MateriController.php` (baru)
- `app/Http/Controllers/Guru/TugasController.php` (baru)
- `app/Http/Controllers/Siswa/MateriController.php` (baru)
- `app/Http/Controllers/Siswa/TugasController.php` (baru)
- `resources/views/guru/materi/`, `guru/tugas/` (baru)
- `resources/views/siswa/materi/`, `siswa/tugas/` (baru)

---

## Fase H — Mobile App (Flutter)

**Tujuan**: Aplikasi Android (& iOS) untuk siswa, guru, dan orangtua.

**Dependencies**: Fase D (API) HARUS selesai dulu

### Checklist

#### Setup
- [ ] Install Flutter SDK
- [ ] Buat project: `flutter create siakad_mobile`
- [ ] Setup dependencies di `pubspec.yaml`:
  - `dio` — HTTP client
  - `flutter_secure_storage` — simpan token
  - `provider` atau `riverpod` — state management
  - `firebase_messaging` — push notification (FCM)
  - `go_router` — routing
  - `cached_network_image` — gambar dari network
  - `fl_chart` — chart sederhana

#### Backend (tambahan untuk FCM)
- [ ] Tambah kolom `fcm_token` ke tabel `users`
- [ ] Buat endpoint `PUT /api/v1/me/fcm-token` untuk simpan token FCM
- [ ] Setup Firebase project & download `google-services.json`
- [ ] Update `WhatsAppService` atau buat `PushNotificationService` untuk kirim FCM

#### Modul App
- [ ] **Auth**: Login, logout, simpan token Sanctum
- [ ] **Siswa**:
  - Dashboard (ringkasan nilai, absensi, tagihan)
  - Jadwal pelajaran (per hari)
  - Nilai & Raport
  - Rekap Absensi
  - SPP — lihat tagihan & bayar via Midtrans Snap (WebView atau SDK)
  - PKL — jurnal harian & absensi check-in/out
  - Kesiswaan — pelanggaran, prestasi, ekskul
  - Pengumuman & Notifikasi
- [ ] **Guru**:
  - Dashboard
  - Jadwal mengajar
  - Input Absensi (ceklis per siswa)
  - Input Nilai (form nilai per siswa)
  - PKL bimbingan
  - Notifikasi
- [ ] **Orangtua**:
  - Dashboard (pilih anak)
  - Nilai & Absensi anak
  - Tagihan SPP & Bayar
  - Notifikasi
- [ ] Push Notification (FCM):
  - Terima notifikasi saat app background/killed
  - Deep link ke halaman terkait saat notif diklik
- [ ] Build APK untuk testing
- [ ] Publish ke Google Play Store

**Struktur folder Flutter:**
```
lib/
├── core/
│   ├── api/          # Dio client & endpoints
│   ├── models/       # Data models
│   └── services/     # Auth service, notification service
├── features/
│   ├── auth/
│   ├── siswa/
│   ├── guru/
│   └── orangtua/
├── shared/
│   ├── widgets/      # Reusable components
│   └── utils/
└── main.dart
```

---

## Estimasi Waktu

| Fase | Fitur | Estimasi |
|------|-------|----------|
| A | Payment Gateway SPP (Midtrans) | 1–2 minggu |
| B | Role Orangtua / Wali Murid | 1–2 minggu |
| C | WhatsApp Notification | 1 minggu |
| D | REST API + Laravel Sanctum | 2–3 minggu |
| E | 2FA & Audit Log | 1 minggu |
| F | Dashboard Analytics | 1–2 minggu |
| G | E-Learning Sederhana | 2–3 minggu |
| H | Mobile App Flutter | 4–6 minggu |

**Total estimasi**: ~3–5 bulan (solo developer, full focus)

---

## Catatan Teknis Penting

### Urutan yang Direkomendasikan
```
A → B → C → D → E → F → G → H
```
- Fase A & B harus berurutan (orangtua butuh payment gateway)
- Fase C bisa paralel setelah A
- Fase D adalah prerequisite untuk Fase H
- Fase E, F, G bisa dikerjakan paralel setelah D

### Stack Tambahan yang Diperlukan
- **Queue Worker**: `php artisan queue:work` (untuk WA notification async)
- **Scheduler**: `php artisan schedule:run` setiap menit (untuk reminder SPP)
- **Storage**: Pastikan `php artisan storage:link` sudah dijalankan
- **Firebase**: Project baru untuk FCM (push notification mobile)

### Environment Variables Tambahan

```env
# Midtrans
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false

# WhatsApp (Fonnte)
FONNTE_TOKEN=
FONNTE_SENDER=
WA_NOTIFICATION_ENABLED=true

# Firebase FCM
FIREBASE_PROJECT_ID=
FIREBASE_CREDENTIALS_PATH=

# Queue
QUEUE_CONNECTION=database
```

### Packages yang Perlu Di-install

```bash
# Fase A
composer require midtrans/midtrans-php

# Fase D
composer require laravel/sanctum

# Fase E
composer require pragmarx/google2fa-laravel
composer require bacon/bacon-qr-code  # untuk QR code 2FA
```

---

*File ini dibuat: 2026-05-27*
*Update file ini setiap fase selesai dengan mencentang checklist yang relevan.*
