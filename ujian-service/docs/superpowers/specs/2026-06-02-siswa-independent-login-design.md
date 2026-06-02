# Spec: Login Siswa Independen + Flow Ujian (Tanpa SSO)

**Tanggal:** 2026-06-02
**Service:** `ujian-service`
**Status:** Draft — menunggu review

## 1. Tujuan

Siswa mengakses ujian-service melalui **login mandiri** (username + password lokal), tanpa
bergantung pada JWT handoff / SSO dari SIAKAD. Setelah login, siswa dapat melihat **daftar
ujian** dan **mengerjakan ujian penuh** (mulai sesi → jawab → submit → lihat hasil).

Admin/guru **tetap** memakai JWT handoff SSO dari SIAKAD (di luar scope perubahan ini).

## 2. Ruang Lingkup

**Termasuk:**
- Entry point: SIAKAD mengarahkan siswa ke halaman login ujian-service (bukan `ujian.sso`).
- Provisioning akun siswa lokal (`user_siswa`) via sinkronisasi dari SIAKAD.
- Login siswa berbasis session (guard `siswa`) yang berfungsi end-to-end.
- Daftar ujian (halaman HTML) sebagai landing pasca-login.
- Flow pengerjaan ujian penuh: start sesi, render soal, simpan jawaban, submit, hasil.
- Jembatan auth: middleware yang menginject `_auth_user` dari session siswa sehingga
  `Api\SesiController` yang sudah ada bisa dipakai ulang tanpa perubahan.

**Tidak termasuk (out of scope):**
- Perubahan flow auth admin/guru (tetap JWT handoff).
- Pembangunan ulang engine ujian (`Api\SesiController` dipakai apa adanya).
- Fitur proktor lanjutan di luar pencatatan violation yang sudah ada.

## 3. Kondisi Saat Ini (temuan)

| Komponen | Status |
|----------|--------|
| Login siswa lokal (`SiswaAuthController`, guard `siswa`, tabel `user_siswa`) | Ada, validasi password lokal (`Hash::check`) |
| Provisioning (`siakad:sync-siswa` + `SiakadSyncService` + endpoint SIAKAD `syncSiswa`) | Ada; password default = NIS |
| Engine ujian (`Api\SesiController`: index/store/show/submitAnswer/submit/result/violation) | Ada, **return JSON**, baca identitas dari `$request->input('_auth_user')` |
| View pengerjaan (`siswa/ujian/index`, `siswa/sesi/show`, `siswa/sesi/result`) | Ada |
| Route web siswa untuk ujian (`siswa.ujian.index` asli, `siswa.sesi.*`) | **Hilang** — hanya placeholder |
| Alias middleware `jwt.verify`, `exam.role` | **Tidak terdaftar** di `bootstrap/app.php` |
| Entry SIAKAD | Mengarah ke `route('ujian.sso')` (JWT handoff) untuk semua role |

### Akar masalah ketergantungan SSO
Engine ujian membaca identitas dari `_auth_user` (kontrak JWT), sementara login web siswa
memakai session guard `auth.siswa`. Keduanya tidak tersambung, dan route web pengerjaan
belum ada — sehingga satu-satunya jalan masuk siswa saat ini adalah via JWT handoff.

## 4. Pendekatan: Session Bridge

Inti solusi: **adaptor**, bukan penulisan ulang.

1. Tambah middleware `InjectSiswaAuthContext` yang — jika `Auth::guard('siswa')->check()` —
   menginject ke request:
   ```php
   $request->merge(['_auth_user' => [
       'user_id'  => $siswa->id,        // local siswa.id (ujian-service)
       'role'     => 'siswa',
       'kelas_id' => $siswa->kelas_id,
       'nama'     => $siswa->nama_lengkap,
       'nis'      => $siswa->nis,
   ]]);
   ```
   Ini memenuhi kontrak `_auth_user` yang sudah dipakai `Api\SesiController` (yang query
   `where('siswa_id', $authUser['user_id'])` dan `$authUser['kelas_id']`).

2. Daftarkan route web siswa (di `routes/web.php`, grup `auth.siswa` + bridge) yang:
   - Menyajikan **halaman HTML** (daftar, kerjakan, hasil) via web controller tipis.
   - Menyajikan **endpoint AJAX JSON** dengan mengarahkan ke `Api\SesiController` yang ada.

3. Karena auth berbasis **cookie session**, endpoint AJAX harus berada di grup **web**
   (punya middleware session + CSRF), bukan `api`. JS view diarahkan ke URL `/siswa/...`
   dan mengirim header `X-CSRF-TOKEN`.

### Kenapa bukan API stateless?
Route `api` tidak punya session cookie; memaksakan session di sana menambah kerumitan
(stateful API, CSRF). Menaruh endpoint siswa di `web` selaras dengan model session login.

## 5. Komponen & Perubahan

### 5.1 Middleware baru
- `app/Http/Middleware/InjectSiswaAuthContext.php` — inject `_auth_user` dari guard `siswa`.
- Daftarkan alias `auth.siswa.context` (atau gabung ke grup) di `bootstrap/app.php`.

### 5.2 Login: lengkapi data session
- `SiswaAuthController::login` saat ini menyimpan `siswa_kelas` (nama) tetapi **bukan
  `kelas_id`**. Bridge mengambil `kelas_id` dari relasi `$userSiswa->siswa->kelas_id`
  (tidak perlu menyimpan ulang di session selama relasi tersedia).
- Verifikasi redirect pasca-login `route('siswa.ujian.index')` mengarah ke daftar ujian asli
  (bukan placeholder).

### 5.3 Web controller siswa (HTML)
`app/Http/Controllers/Siswa/UjianController.php` (atau perluas yang relevan):
- `index()` → render `siswa.ujian.index` dengan koleksi `$ujians` (ujian published untuk
  `kelas_id` siswa, dalam rentang waktu, plus status sesi). Logika mengikuti
  `Api\SesiController::index` namun mengembalikan **view**, bukan JSON.
- `show(SesiUjian $sesi)` → render `siswa.sesi.show` dengan **`$soals` server-rendered**
  (view memakai `let soals = @json($soals)`, bukan fetch). Siapkan `$soals` (acak opsi bila
  perlu, sembunyikan kunci_jawaban selama berlangsung) mengikuti logika `Api\SesiController::show`.
  Validasi kepemilikan sesi (`siswa_id == _auth_user.user_id`).
- `result(SesiUjian $sesi)` → render `siswa.sesi.result` dengan data hasil.

### 5.4 Endpoint AJAX (JSON) — reuse `Api\SesiController`
Route web (grup `auth.siswa` + bridge + CSRF) yang menunjuk ke method existing:
| Method view (fetch) | Route web baru | Handler |
|---------------------|----------------|---------|
| start sesi | `POST /siswa/ujian/{ujian}/sesi` | `SesiController@store` |
| simpan jawaban | `POST /siswa/sesi/{sesi}/answer` | `SesiController@submitAnswer` |
| submit ujian | `POST /siswa/sesi/{sesi}/submit` | `SesiController@submit` |
| catat violation | `POST /siswa/sesi/{sesi}/violation` | `SesiController@violation` |

> Soal **tidak** diambil via AJAX — di-render server-side oleh `show()` (5.3). Method
> `Api\SesiController::index/show/result` (JSON) tidak dipakai langsung oleh view; logika
> penyiapan `$soals` sebaiknya **diekstrak ke method/service bersama** agar web controller
> dan API tidak menduplikasi (acak opsi, sembunyikan kunci, hitung sisa waktu).

### 5.5 Update view JS
`resources/views/siswa/sesi/show.blade.php`:
- Ganti URL fetch dari `/api/sesi/{id}/answer`, `/api/sesi/{id}/submit`, `/api/proktor/event`
  menjadi route web siswa (5.4): `/siswa/sesi/{id}/answer`, `/siswa/sesi/{id}/submit`,
  `/siswa/sesi/{id}/violation`.
- Sertakan `X-CSRF-TOKEN` pada request POST.
- Soal awal tetap server-rendered (`@json($soals)`) — tidak diubah.
- Redirect hasil tetap ke `/siswa/sesi/{id}/result` (route HTML 5.3) — sudah benar di view.

### 5.6 Routing
`routes/web.php` grup `siswa` (`auth.siswa`):
- Ganti placeholder `siswa.ujian.index` menjadi `UjianController@index`.
- Tambah route `siswa.sesi.show`, `siswa.sesi.result`, dan endpoint AJAX (5.4) dengan
  nama yang konsisten (`siswa.sesi.*`).
- Semua di bawah middleware `auth.siswa` + `InjectSiswaAuthContext`.

### 5.7 Entry point SIAKAD
Repo SIAKAD (`/home/roy/siakad-pehape-natip`):
- Tautan ujian untuk **siswa** mengarah ke `config('services.ujian.url') . '/siswa/login'`
  (login mandiri), bukan `route('ujian.sso')`.
- Lokasi: `resources/views/siswa/dashboard.blade.php` (alert ujian) dan
  `resources/views/layouts/partials/sidebar.blade.php` (menu siswa).
- `ujian.sso` / JWT handoff tetap dipakai untuk admin & guru.

### 5.8 Provisioning (verifikasi, bukan bangun baru)
- Pastikan `php artisan siakad:sync-siswa` berjalan: SIAKAD `POST /api/ujian-service/sync/siswa`
  → membuat/meng-update `siswa` lokal + generate `user_siswa` (password default = NIS).
- Dokumentasikan langkah operasional: jalankan sync sebelum siswa login pertama kali;
  jadwalkan berkala bila perlu (`SIAKAD_SYNC_INTERVAL` sudah ada di `.env`).
- Konfirmasi `SIAKAD_BASE_URL` & `SIAKAD_SERVICE_KEY` ujian-service cocok dengan SIAKAD.

## 6. Alur Data (siswa)

```
[SIAKAD dashboard] siswa klik "Ujian Online"
   → redirect ke {UJIAN_SERVICE_URL}/siswa/login
[ujian-service] /siswa/login (guest)
   → POST /siswa/login → validasi user_siswa (Hash::check) → Auth::guard('siswa')->login()
   → redirect siswa.ujian.index
[daftar ujian] UjianController@index (auth.siswa + bridge)
   → siswa pilih ujian → POST /siswa/ujian/{ujian}/sesi (SesiController@store)
   → redirect siswa.sesi.show
[kerjakan] show.blade
   → GET /siswa/sesi/{sesi}/data (soal)
   → POST /siswa/sesi/{sesi}/answer (autosave, X-CSRF-TOKEN)
   → POST /siswa/sesi/{sesi}/violation (tab switch)
   → POST /siswa/sesi/{sesi}/submit
   → redirect /siswa/sesi/{sesi}/result
[hasil] result.blade
```

## 7. Penanganan Error / Edge Case
- Akun terkunci / non-aktif: sudah ditangani `SiswaAuthController` (pesan + lockout 5x/15mnt).
- Sesi bukan milik siswa: web controller & `SesiController` validasi
  `siswa_id == _auth_user.user_id` → 403.
- Sesi kedaluwarsa saat dibuka: `SesiController::show` sudah auto-submit bila lewat waktu.
- Siswa belum ter-provision (login gagal): pesan "Username atau password salah"; remediasi =
  jalankan sync. Dokumentasikan.
- CSRF pada AJAX: token wajib; tanpa token → 419. Pastikan meta `csrf-token` ada di layout.
- SIAKAD down saat login: **tidak berpengaruh** — auth tervalidasi lokal (independen).

## 8. Strategi Testing
- **Unit:** `InjectSiswaAuthContext` menginject `_auth_user` benar untuk siswa ber-session;
  no-op bila tidak login.
- **Feature (HTTP, guard siswa):**
  - Login sukses → redirect daftar ujian; login gagal → error.
  - `GET /siswa/ujian` menampilkan hanya ujian published untuk kelas siswa & dalam rentang waktu.
  - Start sesi → sesi dibuat; tak bisa start milik kelas lain.
  - Submit answer (dengan CSRF) tersimpan; tanpa CSRF → 419.
  - Submit ujian → nilai terhitung; result menampilkan nilai.
  - Akses sesi milik siswa lain → 403.
- **Manual:** jalankan `siakad:sync-siswa`, login pakai NIS, kerjakan satu ujian end-to-end.

## 9. Risiko & Catatan
- `show.blade` memuat soal via `@json($soals)` (server-render) — web controller `show()`
  wajib menyiapkan `$soals`. Logika penyiapan soal (acak opsi, sembunyikan kunci, sisa waktu)
  ada di `Api\SesiController::show`; **ekstrak ke method/service bersama** untuk hindari drift.
- `Api\SesiController` mengembalikan JSON untuk `index/show/result`; halaman HTML memakai
  web controller terpisah (5.3). Delegasikan `store/answer/submit/violation` ke
  `Api\SesiController` agar logika scoring tidak terduplikasi.
- Pastikan tidak ada ketergantungan tersembunyi pada `kelas_id` di tempat lain yang hanya
  tersedia dari JWT.
- `ujian-service` berada di dalam repo SIAKAD yang sama (bukan submodule git terpisah) —
  perubahan kedua sisi di-commit dalam satu repo.
```
