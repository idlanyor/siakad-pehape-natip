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

**Dependencies**: Akun Midtrans (sandbox/production), `composer require midtrans/midtrans-php`

### Checklist

- [x] Install package Midtrans & konfigurasi `.env`
  ```env
  MIDTRANS_SERVER_KEY=
  MIDTRANS_CLIENT_KEY=
  MIDTRANS_IS_PRODUCTION=false
  ```
- [x] Buat `MidtransService` di `app/Services/MidtransService.php`
  - Method `createSnapToken(TagihanSpp $tagihan): array`
  - Method `verifySignature(array $payload): bool`
- [x] Update controller `Siswa/SppController`:
  - Method `getSnapToken(TagihanSpp $tagihan)` — generate snap token, return JSON
  - Tambah tombol "Bayar Online Sekarang" di halaman detail tagihan (Midtrans Snap embed)
- [x] Buat `MidtransWebhookController`:
  - Verifikasi signature dari Midtrans
  - Update status `TagihanSpp` & `PembayaranSpp` otomatis setelah pembayaran sukses/settlement
  - Kirim notifikasi in-app ke siswa & admin
- [x] Tambah route webhook di `routes/web.php` (bypass CSRF)
- [x] Update migration `pembayaran_spps` — tambah `midtrans_order_id`, `midtrans_transaction_id`, `payment_type`
- [x] Buat view Midtrans Snap di halaman SPP siswa (embed Snap.js + fetch token)
- [x] Test end-to-end di sandbox Midtrans
- [x] Update kwitansi PDF/Cetak untuk menyertakan metode pembayaran digital/Midtrans (`$pembayaran->metode_label`)

**File yang dibuat/diubah:**
- `app/Services/MidtransService.php` (baru)
- `app/Http/Controllers/MidtransWebhookController.php` (baru)
- `app/Http/Controllers/Siswa/SppController.php` (update)
- `resources/views/siswa/spp/show.blade.php` (update)
- `database/migrations/2026_05_27_120001_add_midtrans_fields_to_pembayaran_spps_table.php` (baru)
- `resources/views/admin/keuangan/kwitansi.blade.php` (update)
- `routes/web.php` (update)
- `.env` / `.env.example` (update)

---

## Fase B — Role Orangtua / Wali Murid

**Tujuan**: Orangtua punya akun sendiri, bisa pantau anak & bayar SPP.

**Dependencies**: Fase A harus selesai (bayar SPP)

### Checklist

- [x] Tambah `role = 4` (Orang Tua / Wali) di dokumentasi dan middleware `RoleMiddleware`
- [x] Buat migration `create_orang_tuas_table`:
  - `id`, `user_id` (FK), `nama`, `telepon`, `pekerjaan`, `alamat`, `timestamps`
- [x] Buat migration `create_orang_tua_siswa_table` (pivot):
  - `orang_tua_id`, `siswa_id`, `hubungan` (ayah/ibu/wali)
- [x] Buat model `OrangTua` dengan relasi `belongsToMany(Siswa::class)`
- [x] Update model `Siswa` — tambah relasi `orangTua()`
- [x] Update model `User` — tambah `isOrangTua()` method & relasi `orangTua()`
- [ ] Update `Admin/SiswaAdminController` — tambah form tambah/edit orangtua di halaman siswa
- [x] Buat controller `OrangTuaController` (untuk akses admin):
  - CRUD akun orangtua
  - Assign orangtua ke siswa
- [x] Buat controllers untuk dashboard orangtua di `app/Http/Controllers/OrangTua/`:
  - `DashboardController` — pilih anak aktif, ringkasan
  - `AnakController` — switch aktif anak (jika punya >1 anak sekolah di sana)
  - `NilaiController` — lihat nilai anak
  - `AbsensiController` — lihat absensi anak
  - `KesiswaanController` — lihat pelanggaran & prestasi anak
  - `SppController` — lihat tagihan & bayar (gunakan Midtrans dari Fase A)
- [x] Buat views di `resources/views/orangtua/`
- [x] Tambah routes di `routes/web.php`:
  ```php
  Route::middleware(['auth', 'role:4'])->prefix('orangtua')->name('orangtua.')->group(...)
  ```
- [x] Update redirect dashboard utama untuk `role = 4`
- [ ] Notifikasi ke orangtua saat (TODO — butuh hook WA/in-app):
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

**Dependencies**: WA Gateway API Key (`gate.ireng.uk` atau gateway pilihan)

### Checklist

- [x] Setup WA Gateway dengan API Key
- [x] Tambah konfigurasi `.env` & `.env.example`:
  ```env
  WA_API_URL=https://gate.ireng.uk
  WA_API_KEY=
  WA_NOTIFICATION_ENABLED=true
  ```
- [x] Buat `WhatsAppService` di `app/Services/WhatsAppService.php`:
  - Method `send(string $phone, string $message): bool`
  - Handle error & logging
- [x] Tambah kolom `no_wa` (nullable) ke tabel:
  - `siswas` (migration `2026_05_28_010001_add_no_wa_to_siswas_and_gurus_table.php`)
  - `gurus` (migration `2026_05_28_010001_add_no_wa_to_siswas_and_gurus_table.php`)
- [x] Buat template pesan di `app/Services/WaTemplates.php`:
  - `sppJatuhTempo(Siswa $siswa, TagihanSpp $tagihan): string`
  - `sppBerhasil(Siswa $siswa, PembayaranSpp $pembayaran): string`
  - `tagihanBaru(Siswa $siswa, TagihanSpp $tagihan): string`
  - `siswaAlpa(Siswa $siswa, Absensi $absensi): string`
  - `pengumumanBaru(string $judul, string $ringkasan): string`
  - `ppdbDiterima(PendaftarPpdb $pendaftar, string $username, string $password): string`
- [x] Buat Jobs (Queue) untuk setiap notifikasi:
  - `SendWaNotification` job di `app/Jobs/`
  - Dispatch job async agar tidak blocking response
- [x] Trigger notifikasi WA di:
  - `Admin/PembayaranSppController@konfirmasi` → kirim WA ke siswa & orangtua
  - `Guru/AbsensiController@simpan` → kirim WA ke orangtua/nomor siswa jika alpa
  - `Admin/TagihanSppController@store` (bulk create) → kirim WA tagihan baru
  - `Admin/PengumumanController@publish` → kirim WA ke semua target
  - `Admin/PpdbController@terima` → kirim WA ke pendaftar
- [x] Buat halaman log WA di admin (`admin/whatsapp/log`) — lihat history kirim & status via `WaLog`
- [x] Setup queue worker: `php artisan queue:work`
- [x] Setup scheduler untuk reminder SPP jatuh tempo (H-3, H-1) via `KirimReminderSpp` command:
  ```php
  // app/Console/Commands/KirimReminderSpp.php
  Schedule::command('reminder:spp')->dailyAt('08:00');
  ```
- [ ] **TODO**: Tambah toggle on/off notifikasi WA per event di pengaturan admin/CMS

**File yang dibuat/diubah:**
- `app/Services/WhatsAppService.php` (baru)
- `app/Services/WaTemplates.php` (baru)
- `app/Jobs/SendWaNotification.php` (baru)
- `app/Console/Commands/KirimReminderSpp.php` (baru)
- `database/migrations/2026_05_28_010001_add_no_wa_to_siswas_and_gurus_table.php` (baru)
- `database/migrations/2026_05_28_010002_create_wa_logs_table.php` (baru)
- `app/Models/WaLog.php` (baru)
- `app/Http/Controllers/Admin/WaLogController.php` (baru)
- `resources/views/admin/whatsapp/index.blade.php` (baru)
- `routes/web.php` (update)

---

## Fase D — REST API + Laravel Sanctum

**Tujuan**: API lengkap untuk mobile app & integrasi pihak ketiga.

**Dependencies**: Laravel Sanctum

### Checklist

- [x] Install Laravel Sanctum & jalankan migration token
- [x] Setup API routes di `routes/api.php` dengan prefix `/api/v1/`
- [x] Buat middleware `ForceJsonResponse` untuk memaksa response JSON
- [x] **Auth API** (`app/Http/Controllers/Api/AuthController.php`):
  - `POST /api/v1/login` — authenticate & return token
  - `POST /api/v1/logout` — revoke current token
  - `GET  /api/v1/me` — profil user login
  - `PUT  /api/v1/me` — update profil
  - `PUT  /api/v1/me/password` — ganti password
- [x] **Siswa API** (`app/Http/Controllers/Api/Siswa/`):
  - `GET /jadwal` — jadwal pelajaran aktif
  - `GET /nilai` — nilai per semester
  - `GET /absensi` — rekap absensi
  - `GET /raport` — list raport
  - `GET /pkl` — detail PKL
  - `POST /pkl/jurnal` — submit jurnal PKL
  - `POST /pkl/absensi/masuk` — check-in GPS + foto base64
  - `POST /pkl/absensi/pulang` — check-out GPS + foto base64
  - `GET /kesiswaan` — pelanggaran, prestasi, ekskul
  - `GET /spp` — daftar tagihan & detail tagihan
  - `GET /pengumuman` — daftar pengumuman
  - `GET /notifikasi` — daftar notifikasi
  - `POST /notifikasi/{id}/read` — tandai baca
- [x] **Guru API** (`app/Http/Controllers/Api/Guru/`):
  - `GET /jadwal` — jadwal mengajar
  - `GET /absensi` — list jadwal untuk absensi
  - `GET /absensi/{jadwal}` — form absensi
  - `POST /absensi/{jadwal}` — submit absensi
  - `GET /nilai/{jadwal}` — form input nilai
  - `POST /nilai/{jadwal}` — submit nilai
  - `GET /pkl` — list bimbingan PKL
- [x] Rate limiting: 60 req/menit untuk auth, 120 req/menit untuk data
- [x] Buat `API.md` dokumentasi endpoint di `docs/API.md`
- [ ] **TODO**: Orangtua API (`app/Http/Controllers/Api/OrangTua/`) setelah Fase B selesai
- [ ] **TODO**: Export Postman Collection di `docs/api/postman_collection.json`

**File yang dibuat/diubah:**
- `routes/api.php` (update besar)
- `app/Http/Controllers/Api/` (semua controller baru)
- `app/Http/Middleware/ForceJsonResponse.php` (baru)
- `docs/API.md` (baru)

---

## Fase E — 2FA & Audit Log

**Tujuan**: Keamanan berlapis untuk akun admin & pencatatan semua perubahan data.

**Dependencies**: Tidak ada

### Checklist

#### Two-Factor Authentication (2FA)

- [x] Install package: `pragmarx/google2fa-qrcode` + `bacon/bacon-qr-code` (QR SVG)
- [x] Buat migration tambah kolom ke `users`:
  - `two_factor_secret` (text, nullable, encrypted cast)
  - `two_factor_recovery_codes` (text, nullable, encrypted:array)
  - `two_factor_enabled` (boolean, default false)
  - `two_factor_confirmed_at` (timestamp, nullable)
- [x] Buat `TwoFactorController` (show/enable/confirm/disable) + `TwoFactorService`
- [~] Middleware `RequireTwoFactor` — **belum** (2FA bersifat opt-in per user; enforce wajib admin bisa ditambah nanti)
- [x] Tambah step verifikasi OTP saat login (`TwoFactorChallengeController`, session pending)
- [x] Tambah recovery codes untuk backup akses (sekali pakai)
- [x] Halaman profil 2FA — status + aktif/nonaktif + QR + recovery codes
- [x] Link 2FA di navbar (badge "on" saat aktif)

#### Audit Log

- [x] Buat migration `create_audit_logs_table`
- [x] Buat model `AuditLog` (+ action_color/action_label accessor)
- [x] Buat trait `Auditable` — dipasang di Siswa, Guru, Nilai, PembayaranSpp, TagihanSpp, PendaftarPpdb, User
- [x] Buat observer `AuditObserver` — tangkap `created`/`updated`/`deleted`, skip atribut sensitif (password, 2FA secret, akun_password_plain)
- [x] Observer di-register via `bootAuditable()` di trait (bukan AppServiceProvider)
- [x] Halaman audit log admin (`admin/audit-log`): filter user/model/action/tanggal, old vs new values, **export Excel**
- [x] Brute force protection — sudah ada di `LoginRequest` (5x gagal → throttle)

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

**Dependencies**: Tidak ada

### Checklist

- [ ] Install ApexCharts via CDN atau npm
- [ ] Refactor `AdminController@dashboard` — pisahkan query ke `DashboardService`
- [ ] Buat `app/Services/DashboardService.php`:
  - `getStatistikKehadiran()`, `getDistribusiNilai()`, `getStatusSpp()`, `getTrendPpdb()`
- [ ] Update halaman dashboard admin — tambah section charts:
  - **Card stats**: total siswa, guru, kelas, pendapatan SPP bulan ini
  - **Line chart**: Tren kehadiran 6 bulan terakhir
  - **Bar chart**: Distribusi nilai per kelas
  - **Donut chart**: Status SPP (lunas/cicil/belum bayar/tunggakan)
  - **Bar chart**: Statistik PPDB per gelombang
  - **Table**: 5 siswa dengan poin pelanggaran tertinggi & 5 prestasi terbaru
- [ ] Buat halaman **Laporan** khusus (`admin/laporan`):
  - Rekap kehadiran per kelas/semester → export PDF
  - Rekap nilai per kelas/jurusan → export PDF & Excel
  - Laporan keuangan SPP per bulan/tahun → export PDF
  - Laporan PPDB per gelombang → export PDF
- [ ] Filter interaktif: tahun ajaran, semester, kelas, jurusan
- [ ] Print/export chart sebagai gambar

**File yang dibuat/diubah:**
- `app/Services/DashboardService.php` (baru)
- `app/Http/Controllers/AdminController.php` (update)
- `app/Http/Controllers/Admin/LaporanController.php` (baru)
- `resources/views/admin/dashboard.blade.php` (update besar)
- `resources/views/admin/laporan/` (baru)

---

## Fase G — E-Learning Sederhana

**Tujuan**: Guru upload materi & beri tugas online, terintegrasi dengan sistem nilai.

**Dependencies**: Tidak ada

### Checklist

- [x] Buat migrations:
  - `create_materis_table` (materi belajar: file, link, teks)
  - `create_tugas_table` (tugas belajar dengan deadline)
  - `create_pengumpulan_tugas_table` (pengumpulan tugas oleh siswa, nilai & feedback)
- [x] Buat models: `Materi`, `Tugas`, `PengumpulanTugas`
- [x] **Guru controllers**:
  - CRUD Materi per jadwal mengajar
  - CRUD Tugas per jadwal + set deadline
  - Lihat, download berkas, dan beri nilai & feedback pengumpulan tugas siswa
- [x] **Siswa controllers**:
  - `GET /siswa/materi` — list materi per mata pelajaran
  - `GET /siswa/tugas` — daftar tugas + status
  - `POST /siswa/tugas/{id}/kumpul` — upload pengumpulan tugas (file/teks/link)
- [x] Tambah menu navigasi di sidebar guru & siswa
- [ ] **TODO**: Notifikasi in-app & WhatsApp saat materi/tugas baru dibuat, atau saat tugas selesai dinilai
- [ ] **TODO**: Tambah API endpoints untuk materi & tugas di `/api/v1/` (Fase D)

**File yang dibuat/diubah:**
- `database/migrations/2027_05_27_110001_create_materis_table.php` (baru)
- `database/migrations/2027_05_27_110002_create_tugas_table.php` (baru)
- `database/migrations/2027_05_27_110003_create_pengumpulan_tugas_table.php` (baru)
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

**Dependencies**: Fase D (API) harus selesai terlebih dahulu.

### Checklist

#### Setup
- [ ] Install Flutter SDK & setup project `siakad_mobile`
- [ ] Setup dependencies: `dio`, `flutter_secure_storage`, state management, `firebase_messaging` (FCM), router
- [ ] Tambah kolom `fcm_token` ke tabel `users`
- [ ] Buat endpoint `PUT /api/v1/me/fcm-token` untuk menyimpan token FCM
- [ ] Setup Firebase Project & Push Notification Service di backend

#### Modul App
- [ ] **Auth**: Login, logout, local session storage (Sanctum Token)
- [ ] **Siswa**: Dashboard, jadwal pelajaran, nilai & raport, rekap absensi, bayar SPP (Midtrans Snap WebView), PKL (jurnal & absensi geolocation), kesiswaan, notifikasi
- [ ] **Guru**: Dashboard, jadwal mengajar, input absensi, input nilai, bimbingan PKL, notifikasi
- [ ] **Orangtua**: Dashboard anak, nilai & absensi anak, tagihan SPP & pembayaran anak, notifikasi
- [ ] Push Notification (FCM): auto-route ke menu detail terkait saat notifikasi diklik
- [ ] Build APK & release to Play Store / App Store

---

## Estimasi Waktu

| Fase | Fitur | Status | Estimasi |
|------|-------|--------|----------|
| A | Payment Gateway SPP (Midtrans) | ✅ Selesai | 1–2 minggu |
| B | Role Orangtua / Wali Murid | 🟦 Inti selesai (notif menyusul) | 1–2 minggu |
| C | WhatsApp Notification | ✅ Selesai | 1 minggu |
| D | REST API + Laravel Sanctum | ✅ Selesai | 2–3 minggu |
| E | 2FA & Audit Log | ✅ Selesai | 1 minggu |
| F | Dashboard Analytics | ✅ Selesai (charts + Laporan) | 1–2 minggu |
| G | E-Learning Sederhana | ✅ Selesai | 2–3 minggu |
| H | Mobile App Flutter | ⬜ Rencana | 4–6 minggu |

---

## Catatan Teknis Penting

### Urutan yang Direkomendasikan
```
A → C → D → G → B → E → F → H
```
- Fase A, C, D, dan G saat ini telah selesai diimplementasikan di backend.
- Langkah selanjutnya adalah melengkapi fitur **Fase B (Orangtua)** sebelum membangun **Fase H (Mobile App)** agar modul Orangtua di aplikasi mobile dapat terintegrasi sepenuhnya.

### Environment & Daemon
- **Queue Worker**: `php artisan queue:work` (wajib berjalan untuk mengirim WhatsApp secara async)
- **Scheduler**: `php artisan schedule:run` di cron server setiap menit (untuk tagihan & reminder SPP)
- **Firebase Project**: Diperlukan untuk setup push notification FCM di Fase H
