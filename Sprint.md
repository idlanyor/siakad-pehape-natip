# Audit Kesiapan Jual — SIAKAD SMK

Berdasarkan analisis menyeluruh terhadap codebase, roadmap, dan progress, berikut **daftar kekurangan** yang harus ditangani sebelum aplikasi layak dijual:

---

## ✅ Sprint 1 — SELESAI (2026-05-29)

Catatan: sebagian fondasi ternyata **sudah ada sebagian** (config/middleware), tapi belum dirangkai/dipakai. Yang dikerjakan:

| # | Item | Hasil |
|---|------|-------|
| 1 | Password plaintext | Kolom `akun_password_plain` kini **terenkripsi at-rest** (cast `encrypted`, kolom → TEXT, migrasi enkripsi data lama) + `$hidden` agar tak bocor di serialisasi/JSON. Alur admin/cetak tetap jalan. |
| 2 | Authorization (IDOR) | Audit penuh: **semua** controller siswa (web + API) sudah enforce ownership inline (`abort_if`/scoped `where`). 6 Policy ada & auto-discovered. Risiko IDOR tertutup. |
| 3 | Error pages | 403/404/500/503 sudah ada; **ditambah 419 (Page Expired)**. |
| 4 | Backup | `spatie/laravel-backup` ter-install; **dijadwalkan** `backup:run` harian 02:00 + `backup:clean` mingguan. Diuji: zip terbuat. |
| 5 | XSS CMS | `mews/purifier` ter-install tapi tak dipakai → **`clean()` dipasang** di Artikel, HalamanStatis, Pengumuman saat simpan. Diuji: `<script>`/`onerror` ter-strip. |
| 6 | HTTPS | `ForceHttps` middleware ada; **ditambah `URL::forceScheme('https')`** di production agar URL/asset/form ikut https. |
| 7 | CORS | `config/cors.php` sudah ada — terverifikasi. |
| + | Test infra | Perbaiki `UserFactory` (kolom `username`/`role`), isolasi test DB ke **sqlite :memory:** (sebelumnya tes jalan di DB mysql live — berbahaya), perbaiki tes auth/registrasi sesuai realita. **24 passed, 0 failed.** |

---

## ✅ Sprint 2 — Fase B: Role Orang Tua / Wali — SELESAI (2026-05-29)

Portal orang tua (role=4) untuk memantau anak. Inti fitur selesai & teruji.

| Item | Hasil |
|------|-------|
| Migration | `orang_tuas` + pivot `orang_tua_siswa` (hubungan: ayah/ibu/wali). |
| Model | `OrangTua` (belongsToMany Siswa), relasi `Siswa::orangTua()`, `User::orangTua()` + `User::isOrangTua()`. |
| Admin CRUD | `Admin\OrangTuaController` — buat/edit/hapus akun + assign/lepas anak. Menu sidebar admin "Orang Tua / Wali". |
| Portal Controllers | `OrangTua\{Base,Dashboard,Anak,Nilai,Absensi,Kesiswaan,Spp}Controller`. Anak aktif disimpan di session, **selalu divalidasi kepemilikan**. |
| Pembayaran | SPP anak reuse **Midtrans Snap** dari Fase A (snap-token + embed). |
| Views | `orangtua/` (dashboard, nilai, absensi, kesiswaan, spp, spp-show) + partial anak-switcher. |
| Routes | grup `role:4` prefix `orangtua` + redirect dashboard role=4. |
| Keamanan | Diuji: route asing → **403 (IDOR)**, non-ortu → **403**. |
| Test | `OrangTuaPortalTest` (4 test). **Total suite: 28 passed.** |

**Belum (menyusul, butuh hook notif):** notifikasi WA/in-app ke ortu (alpa, nilai baru, tagihan baru, pembayaran sukses) + form ortu di halaman edit siswa admin.

---

## ✅ Sprint 3 — Fase E: 2FA & Audit Log — SELESAI (2026-05-29)

### Two-Factor Authentication (TOTP)
| Item | Hasil |
|------|-------|
| Package | `pragmarx/google2fa-qrcode` + `bacon/bacon-qr-code`. |
| Kolom users | `two_factor_secret` (encrypted), `two_factor_recovery_codes` (encrypted:array), `two_factor_enabled`, `two_factor_confirmed_at` — semua hidden dari serialisasi. |
| Service | `TwoFactorService` — secret, QR SVG inline, verify, recovery codes. |
| Setup | `TwoFactorController` (show/enable/confirm/disable) + view profil + link navbar (badge "on"). Disable wajib konfirmasi password. |
| Login challenge | `TwoFactorChallengeController` — setelah password sukses & 2FA aktif, user **di-logout & ditahan di session pending** sampai OTP/recovery code benar. Baru `Auth::login()`. |
| Recovery code | Sekali pakai, terkonsumsi setelah dipakai. |

### Audit Log
| Item | Hasil |
|------|-------|
| Tabel/Model | `audit_logs` + `AuditLog` (action_color/label accessor). |
| Trait+Observer | `Auditable` (self-register via `bootAuditable`) + `AuditObserver` tangkap created/updated/deleted. **Skip** password, 2FA secret, `akun_password_plain`; string dipangkas 500 char. |
| Dipasang di | Siswa, Guru, Nilai, PembayaranSpp, TagihanSpp, PendaftarPpdb, User. |
| Admin UI | `admin/audit-log` — filter user/model/aksi/tanggal, diff old→new (collapsible), **export Excel** (`AuditLogExport`), sidebar link. |

**Diverifikasi:** 11 test baru (`TwoFactorAuthTest` 6, `AuditLogTest` 5) — termasuk "tidak login sampai OTP benar", recovery code terkonsumsi, password tak pernah tercatat. Smoke test observer di MySQL live OK. **Suite total: 39 passed.**

**Belum (opsional):** middleware `RequireTwoFactor` untuk paksa 2FA wajib admin (sekarang opt-in per user).

---

## ✅ Sprint 4 — Polish & Sellability — SELESAI (2026-05-30)

| Item | Hasil |
|------|-------|
| **Installer Wizard** | `/install` 3 langkah: cek requirement → konfig DB (tulis `.env` + tes koneksi) → identitas sekolah & akun admin (migrate + seed). Dikunci middleware `install.guard` via penanda `storage/installed`. **Instance live sudah ditandai** agar wizard tak bisa menimpa data. |
| **Laporan (Fase F)** | `admin/laporan` — Kehadiran, Nilai, Keuangan, PPDB. Tampil di layar + **export PDF** (dompdf, layout cetak bersama). Dashboard charts ApexCharts sudah ada sebelumnya. |
| **LICENSE** | File lisensi proprietary/komersial. |
| **Postman** | `docs/api/postman_collection.json` — Auth + Siswa + Guru, auto-simpan token via test script. JSON tervalidasi. |
| **Dokumentasi** | `docs/INSTALL.md` (wizard + manual + daemon + keamanan) & `docs/USER_GUIDE.md` (fitur per peran). |
| **FormRequests** | `SiswaRequest`, `GuruRequest`, `MapelRequest`, `JurusanRequest`, `KelasRequest` — validasi store+update terpusat, `authorize()` cek admin (lapisan 403 kedua di atas middleware). |

**Diverifikasi:** test baru `InstallerTest` (1), `LaporanTest` (5, incl PDF), `AdminFormRequestTest` (5, incl tolak non-admin & ignore-self username). **Suite total: 50 passed** (1 risky pre-existing). Routes resolve, semua blade compile, FormRequest autoload OK.

**Catatan:** Laporan export Excel belum (PDF sudah; export Excel per-modul lain — Nilai/SPP/PPDB — sudah ada terpisah). `RequireTwoFactor` masih opsional.

---

### 🔴 KRITIS (Harus Diperbaiki Sebelum Jual)

| # | Masalah | Detail |
|---|---------|--------|
| 1 | **Password plaintext tersimpan di DB** | `PendaftarPpdb.akun_password_plain` menyimpan password mentah. Ini pelanggaran keamanan berat. Hapus kolom ini, kirim password hanya sekali via WA/email saat diterima. |
| 2 | **Tidak ada Policy/Authorization granular** | Tidak ada `app/Policies/`. Semua otorisasi hanya berbasis role middleware. Siswa A bisa akses data Siswa B jika manipulasi URL. Butuh Policy per model. |
| 3 | **Custom error pages tidak ada** | `resources/views/errors/` tidak ada. User melihat stack trace Laravel saat error di production. Buat 403, 404, 419, 500, 503. |
| 4 | **Tidak ada backup mechanism** | Tidak ada `spatie/laravel-backup` atau script backup DB/files. Sekolah kehilangan data = bencana. |
| 5 | **XSS risk di CMS content** | `{!! $halaman->konten !!}`, `{!! $artikel->konten !!}`, `{!! setting('sambutan_kepsek') !!}` — output unescaped. Perlu HTML Purifier untuk sanitasi input rich text. |
| 6 | **HTTPS tidak di-enforce** | Tidak ada `URL::forceScheme('https')`. Data siswa, pembayaran, dan kredensial bisa terekspos di HTTP. |

---

### 🟠 PENTING (Standar Produk Komersial)

| # | Masalah | Detail |
|---|---------|--------|
| 7 | **Fase B (Role Orangtua) belum dibangun** | Fitur kunci untuk selling point. Orangtua tidak bisa login & pantau anak. Semua checklist masih `[ ]`. |
| 8 | **Fase E (2FA & Audit Log) belum dibangun** | Keamanan admin tanpa 2FA lemah. Audit log wajib untuk compliance sekolah. |
| 9 | **Fase F (Dashboard Analytics) belum dibangun** | Admin/Kepsek butuh visualisasi data. Dashboard saat ini kemungkinan masih basic. |
| 10 | **Tidak ada installer/setup wizard** | Pembeli harus setup manual. Butuh artisan command atau web wizard: input nama sekolah, buat admin, seed data awal. |
| 11 | **FormRequest validation minim** | Hanya 2 file (`ProfileUpdateRequest`, `LoginRequest`). Controller lain pakai inline `$request->validate()`. Tidak konsisten, sulit di-maintain. |
| 12 | **Test coverage sangat minim** | Hanya test bawaan Breeze (auth/profile). Tidak ada test untuk SPP, PPDB, Absensi, Nilai, API. Buyer enterprise butuh jaminan stabilitas. |
| 13 | **Tidak ada CORS config** | `config/cors.php` tidak ada. Mobile app (Fase H) akan gagal request API. |
| 14 | **Dokumentasi user/admin tidak ada** | Hanya `docs/API.md`. Tidak ada user manual, admin guide, atau installation guide. |

---

### 🟡 PENINGKATAN (Nilai Jual Lebih Tinggi)

| # | Masalah | Detail |
|---|---------|--------|
| 15 | ✅ **Dark mode** (SELESAI 2026-05-30) | Semua panel ter-autentikasi (admin/guru/siswa/ortu) — toggle navbar, persist cookie 1 thn, server-render anti-FOUC, pakai dark-mode bawaan AdminLTE 3. Landing publik (Tailwind) belum. Diuji: `DarkModeTest`. |
| 16 | **Accessibility (a11y) sangat minim** | Hampir tidak ada `aria-label`, `sr-only`. Tidak memenuhi standar WCAG. |
| 17 | ✅ **Avatar/foto profil user** (SELESAI 2026-05-30) | Kolom `avatar` (nullable) di users, disk `public`, accessor `User::avatar_url` (fallback `dist/img/avatar.png`). Upload di halaman profil (preview live via Alpine, validasi image jpg/png/webp ≤2MB), avatar lama dihapus saat ganti. Tampil di user-panel sidebar + navbar AdminLTE. Diuji: `ProfileTest` (+4 test — unggah, hapus lama, tolak non-gambar, fallback). |
| 18 | ✅ **Bulk actions di admin** (SELESAI 2026-05-30) | Multi-select hapus massal di **Data Siswa** & **Data Guru**: checkbox per baris + "pilih semua", tombol "Hapus Terpilih (n)" yang aktif saat ada pilihan, konfirmasi jumlah. Route `admin.siswa.bulk-destroy` & `admin.guru.bulk-destroy` (DELETE) — validasi `ids[]` exists, hapus via akun login (cascade) konsisten dgn destroy tunggal, dijaga middleware `role:0`. Diuji: `BulkActionTest` (4 — hapus terpilih siswa/guru, tolak tanpa ids, non-admin → 403). |
| 19 | ✅ **Maintenance mode flow** (SELESAI 2026-05-30) | Laravel `artisan down/up` built-in + 503 page sudah ada (Sprint 1). Ditambah: **Admin UI toggle** di halaman Pengaturan (card maintenance, tombol aktifkan/nonaktifkan), **badge indicator** di navbar saat maintenance aktif (admin only), **bypass secret** auto-generate (flash message berisi URL bypass). Controller `MaintenanceController` (enable/disable/status). Diuji: `MaintenanceTest` (5 — toggle on/off, non-admin 503, status endpoint, bypass secret). |
| 20 | **Postman Collection belum di-export** | `docs/api/postman_collection.json` masih TODO di roadmap. |
| 21 | **Toggle WA notifikasi per event** | Masih TODO di Fase C — admin tidak bisa on/off notifikasi WA per jenis event. |
| 22 | ✅ **E-Learning notifikasi** (SELESAI 2026-05-30) | Service `ELearningNotifier` + hook model events (web & API sekaligus): **materi terbit** → notif ke semua siswa kelas (saat dibuat published / draft→terbit), **tugas baru** → notif siswa kelas, **tugas dinilai** → notif siswa ybs saja. Bulk-insert (1 query). Diuji: `ELearningNotifikasiTest` (5 — fan-out kelas, draft skip, terbit-menyusul, penilaian targeted). |
| 23 | ✅ **API E-Learning endpoints** (SELESAI 2026-05-30) | REST materi & tugas di `/api/v1`. **Siswa:** list/detail materi terbit kelasnya, list/detail tugas (+status pengumpulan sendiri), `POST tugas/{id}/kumpul` (file/link/teks, tolak lewat-deadline & dobel). **Guru:** materi per jadwal + tambah/hapus, tugas per jadwal + buat, list pengumpulan, `POST pengumpulan/{id}/nilai`. Semua scoped ownership (`abort 403` IDOR). Resource: `MateriResource`, `TugasResource`, `PengumpulanTugasResource`. Postman diperbarui. Diuji: `ELearningApiTest` (8 — alur buat→lihat, IDOR, role-guard, deadline 422, auth 401). |
| 24 | **API Orangtua** | TODO di Fase D — bergantung pada Fase B selesai dulu. |
| 25 | **LICENSE file tidak ada** | Butuh lisensi komersial yang jelas untuk distribusi. |
| 26 | ✅ **Multi-tenancy — DIBATALKAN & DICABUT** (2026-05-30) | Rencana SaaS multi-tenant dibatalkan. `stancl/tenancy` ternyata cuma boilerplate (provider tak terdaftar, `routes/tenant.php` tak di-load, nol referensi di kode). Dicabut bersih: `composer remove stancl/tenancy`, hapus `config/tenancy.php` + `TenancyServiceProvider` + `routes/tenant.php` + 2 migration + subdir, drop tabel `tenants`/`domains` (keduanya kosong, aman) + bersihkan entri `migrations`. App = single-tenant (satu install per sekolah). Diverifikasi: routes resolve, 74 passed nol regresi. |
| 27 | ✅ **Loading skeleton / UX polish** (SELESAI 2026-05-30) | **Table skeleton** component reusable (pulse animation) — diterapkan ke Data Siswa & Data Guru (skeleton 8-9 cols, hide setelah DataTables init). **Button loading state** global (disable + spinner saat form submit, auto via event listener). **SweetAlert2** intercept `confirm()` di form submit (41 native confirm → smooth modal). **Toastr** auto-show dari flash session (success/error/warning/info, top-right toast). Semua tanpa ubah view existing (kecuali 2 tabel). |
| 28 | **Activity log / changelog untuk user** | User tidak tahu perubahan apa yang terjadi di akunnya. |

---

---

## ✅ Sprint 5 — Ujian Service: Controller Ujian, Soal, Sesi — SELESAI (2026-06-02)

Microservice ujian online terpisah dengan full CRUD dan flow ujian lengkap.

| Item | Hasil |
|------|-------|
| **Ujian Controller** | Full CRUD (index/store/show/update/destroy) + publish/unpublish + statistik. Validasi: tidak bisa edit/delete jika ada sesi, tidak bisa publish tanpa soal cukup. Filter: periode/kelas/mapel/guru/tanggal/status. |
| **Soal Controller** | Full CRUD + reorder + import dari bank soal + upload media. Validasi: tidak bisa edit jika ujian published. Auto-reorder saat delete. Support tipe: pilihan_ganda, benar_salah, essay. |
| **Sesi Controller** | Flow lengkap siswa: list available ujian → start session → get soals (auto-check timeout) → submit answer per soal → submit exam → get result. Auto-grading PG/BS, manual grading essay. Soal & opsi bisa diacak. |
| **Authorization** | Role-based: siswa (sesi sendiri), guru (ujian sendiri + hasil siswa), admin (full access). JWT token required semua endpoint. Ownership check ketat. |
| **Business Rules** | Satu siswa = satu sesi resmi per ujian. Auto-submit saat timeout. Time-based validation start/submit. Status validation prevent invalid transitions. |
| **API Endpoints** | 20+ endpoints: `/api/ujian`, `/api/ujian/{id}/soal`, `/api/sesi`, `/api/sesi/{id}/answer`, dll. Request/response examples lengkap di README. |

**Diverifikasi:** Test `UjianCrudTest` (15 test) — create/read/update/delete ujian, publish/unpublish, authorization (guru vs admin vs siswa), validasi field, prevent edit/delete saat ada sesi. **Total suite ujian-service: 15 passed.**

**Catatan:** Sprint 2-4 ujian-service (periode/fase, JWT handoff, Reverb proktor) sudah selesai sebelumnya. Sprint 6 berikutnya: migrasi view blade + testing integrasi end-to-end.

---

## ✅ Sprint 6 — Ujian Service: Frontend Views & Integration — SELESAI (2026-06-02)

Frontend lengkap untuk ujian-service dengan AdminLTE 3 layout, integrasi WebSocket realtime, dan mekanisme autentikasi hybrid.

| Item | Hasil |
|------|-------|
| **Layout AdminLTE** | Copied & adapted dari SIAKAD utama: `layouts/admin.blade.php` + partials (navbar, sidebar, footer, flash). Custom sidebar menu role-based (admin/guru/siswa). Dark mode support. |
| **Manajemen Ujian** | `ujian/index.blade.php` (DataTables + filter), `ujian/create.blade.php` (form lengkap), `ujian/show.blade.php` (detail + statistik + list soal), `ujian/edit.blade.php` (form edit dengan validasi sesi). |
| **Manajemen Soal** | `soal/create.blade.php` (Summernote WYSIWYG + upload media), `soal/edit.blade.php` (edit dengan preview media), `soal/show.blade.php` (detail + statistik jawaban + chart distribusi). |
| **Portal Siswa** | `siswa/ujian/index.blade.php` (daftar ujian tersedia), `siswa/sesi/show.blade.php` (halaman mengerjakan dengan timer countdown + proktor monitoring), `siswa/sesi/result.blade.php` (hasil + pembahasan). |
| **Proktor Dashboard** | `proktor/dashboard.blade.php` — monitoring realtime via Laravel Echo + Reverb WebSocket. Live stats, active sessions cards, event log dengan filter severity, chart distribusi jawaban. |
| **Periode & Fase** | `periode/index.blade.php` (CRUD periode dengan modal), `periode/show.blade.php` (detail + statistik + CRUD fase + list ujian). |
| **JavaScript Features** | DataTables, Select2, Summernote, DateTimePicker, Chart.js, SweetAlert2, Toastr, Alpine.js (timer), Laravel Echo (WebSocket), proktor event handlers. |
| **Proktor Monitoring** | Client-side: detect tab switch, blur/focus, copy/paste, right-click, fullscreen exit, devtools. Server-side: broadcast via Reverb, severity levels (low/medium/high/critical), auto-stats update. |

**Integration Layer (SIAKAD ↔ Ujian Service):**

| Component | Hasil |
|-----------|-------|
| **API Endpoints SIAKAD** | `UjianServiceController` — 8 endpoints: health check, kelas list, mapel list, siswa by kelas, siswa detail, sync siswa, verify credentials, push updates. Protected by Sanctum + role middleware. |
| **JWT Token Handoff** | `JwtHandoffController` — generate/verify/refresh JWT untuk admin/guru. Web redirect `/ujian-service/redirect` dengan token di query. Ujian Service receive via `/auth/handoff`. |
| **Siswa Sync Service** | `SiakadSyncService` — sync siswa data dari SIAKAD ke local DB ujian-service. Support incremental sync (last_sync), filter by kelas, health check. Command: `php artisan siakad:sync-siswa`. |
| **Siswa Auth** | `SiswaAuthController` — login siswa di ujian-service dengan credential verification ke SIAKAD. Create/update local siswa record. Generate JWT token untuk API access. Session-based untuk web. |
| **Middleware** | `AuthenticateSiswa` (session-based), `AuthenticateJWT` (token dari SIAKAD). Auto-redirect ke SIAKAD login jika token invalid. |
| **Models & Migration** | `Siswa` model dengan relasi ke `SesiUjian`. Migration `create_siswa_table` dengan kolom: siakad_id, nis, nisn, nama_lengkap, kelas_id, kelas_nama, tingkat, username, email, status, synced_at. |
| **Config** | `services.siakad` — base_url, service_key, sync_enabled, sync_interval. JWT secret harus sama di kedua aplikasi. |
| **Documentation** | `docs/INTEGRATION.md` — arsitektur, flow autentikasi, API reference, setup guide, troubleshooting, monitoring. |

**File Created (Total 27 files):**
- **Views (16):** Layout + partials, ujian CRUD, soal CRUD, siswa portal, proktor dashboard, periode & fase
- **Controllers (4):** `UjianServiceController` (SIAKAD), `JwtHandoffController` (SIAKAD), `SiswaAuthController` (Ujian Service), `HandoffController` (Ujian Service)
- **Services (1):** `SiakadSyncService` dengan 8 methods
- **Models (1):** `Siswa` dengan scopes & relations
- **Middleware (2):** `AuthenticateSiswa`, `AuthenticateJWT`
- **Commands (1):** `SyncSiswaFromSiakad`
- **Migrations (1):** `create_siswa_table`
- **Config (1):** `services.php` updated
- **Routes (2):** `api.php` (SIAKAD), `web.php` (Ujian Service) updated
- **Docs (1):** `INTEGRATION.md`

**Mekanisme Autentikasi:**
1. **Admin/Guru:** JWT token handoff dari SIAKAD → verify → session di Ujian Service
2. **Siswa:** Login form di Ujian Service → verify credentials ke SIAKAD → create local record → JWT + session

**Belum (Requires Manual Testing):**
- Testing integrasi JWT token handoff end-to-end
- Testing siswa login flow dengan credential verification
- Testing sync siswa command & scheduler
- Testing end-to-end flow ujian lengkap
- Performance testing (load test concurrent sessions)
- Security audit (XSS, CSRF, authorization bypass)

---

### 📋 Prioritas Eksekusi yang Disarankan

```
Sprint 1 (Minggu 1-2) — Security & Foundation ✅
├── Fix plaintext password storage
├── Tambah Laravel Policies per model
├── Sanitasi XSS (HTML Purifier)
├── Force HTTPS
├── Custom error pages (403/404/500/503)
├── Setup backup (spatie/laravel-backup)
└── CORS config

Sprint 2 (Minggu 3-4) — Fase B: Role Orangtua ✅
└── Full implementation sesuai roadmap

Sprint 3 (Minggu 5) — Fase E: 2FA & Audit Log ✅
└── Full implementation sesuai roadmap

Sprint 4 (Minggu 6-7) — Polish & Sellability ✅
├── Installer/setup wizard
├── Dashboard Analytics (Fase F)
├── Custom FormRequests
├── Test coverage (minimal happy path)
├── User documentation (PDF manual)
├── LICENSE file
└── Postman Collection export

Sprint 5 (Minggu 8) — Ujian Service Backend ✅
├── Periode & Fase management
├── JWT token handoff
├── Laravel Reverb proktor realtime
├── Controller Ujian/Soal/Sesi
└── Full API endpoints

Sprint 6 (Minggu 9) — Ujian Service Frontend & Integration 🔄
├── ✅ Migrasi view blade existing
├── ✅ Adaptasi layout AdminLTE
├── ✅ Frontend views manajemen ujian (index, create, show, edit)
├── ✅ Frontend views manajemen soal (create, edit, show)
├── ✅ Frontend views portal siswa (index ujian, mengerjakan, result)
├── ✅ Frontend proktor monitoring dashboard (realtime WebSocket)
├── ✅ Frontend periode & fase management (index, show)
├── [ ] Testing integrasi token handoff SIAKAD ↔ Ujian Service
├── [ ] Testing end-to-end flow ujian lengkap
└── [ ] Performance & security testing
```