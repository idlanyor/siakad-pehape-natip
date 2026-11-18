# Rencana: Ujian Online → Modul di SIAKAD (Modular Monolith + Inertia)

Tanggal: 2026-11-18
Status: draft, menunggu eksekusi

## Tujuan

Memindahkan `ujian-service/` ke dalam aplikasi SIAKAD sebagai modul. Hasil akhirnya:

- Satu aplikasi, satu database (`simakad`), satu login (guard `web` SIAKAD).
- Fitur ujian memakai Inertia + Vue 3. Bagian SIAKAD lainnya tetap Blade.
- Tidak ada lagi JWT/SSO/handoff, sync siswa, service key, route stub, atau symlink aset.
- Folder `ujian-service/` dihapus.

## Fakta yang mendasari rencana

| Hal | Kondisi saat ini | Dampak |
|---|---|---|
| Versi framework | SIAKAD dan ujian-service sama-sama Laravel 11, PHP 8.2 | Kode PHP bisa dipindah tanpa upgrade |
| Styling halaman Vue | Pakai class AdminLTE/Bootstrap (`row`, `col-sm-6`, `fas`), bukan Tailwind | Bisa tampil di shell AdminLTE SIAKAD tanpa konflik CSS |
| Data `ujian_db` | Hanya 3 baris `periode_ujian`, tabel lain kosong | Tidak perlu migrasi data; migration boleh di-*squash* |
| Tabel ujian di `simakad` | Tidak ada | Tidak ada konflik nama tabel |
| Sisa kode ujian lama di SIAKAD | `Admin\UjianController`, `HasilUjianController`, `HasilUjianExport`, `SesiUjianObserver`, view `admin/ujian`, `guru/ujian`, `siswa/ujian`, `admin/proktor`, query `SoalUjian` di sidebar | Referensinya ke model yang sudah tidak ada; harus dihapus atau di-port |
| Ukuran kode yang dipindah | ~5.3k baris PHP (controller/service/event), ~1.3k baris model, ~15k baris Vue | Dikerjakan bertahap per fase |

## Struktur target

```
app/Modules/Ujian/
  Models/            PeriodeUjian, FasePeriode, BankSoal, Ujian, SoalUjian,
                     SesiUjian, JawabanSiswa, ProktorEvent, MediaSoal
  Http/Controllers/  Admin/ Guru/ Siswa/ Proktor/   (controller Inertia)
  Http/Requests/
  Policies/          UjianPolicy, SesiUjianPolicy, BankSoalPolicy, PeriodeUjianPolicy
  Services/          SesiUjianService (mulai, jawab, submit, nilai),
                     PenilaianService, ProktorService
  Events/            SesiUpdated, ProktorEvent (realtime: fase 7)
  Exports/           HasilUjianExport (port dari SIAKAD lama)
  routes.php
  UjianServiceProvider.php   (load routes, migrations, policies)
database/migrations/ujian/   migration hasil squash
resources/js/ujian.js        entry Inertia (terpisah dari app.js Blade)
resources/js/Ujian/Pages/, resources/js/Ujian/Components/
resources/views/layouts/inertia.blade.php   root view: shell AdminLTE + @inertia
tests/Feature/Ujian/
```

### Keputusan desain

1. **Root view Inertia memakai shell AdminLTE Blade** (`navbar`/`sidebar` partial SIAKAD). Vue hanya merender area konten, sehingga `AppLayout.vue` dibuang. Konsekuensinya, saat navigasi client-side antar halaman Inertia, state "active" di sidebar Blade tidak ikut berubah. Ini diatasi dengan script kecil di root view yang mendengarkan event `navigate` Inertia. Porting sidebar (600+ baris dengan logika role) ke Vue tidak sepadan usahanya.
2. **Middleware `HandleInertiaRequests` hanya dipasang di grup route ujian**, tidak global, supaya halaman Blade lain tidak tersentuh.
3. **Relasi langsung ke model SIAKAD**: `sesi_ujian.siswa_id` menjadi FK ke `siswas.id`, sedangkan `ujian.kelas_id`, `mapel_id`, dan `guru_id` menjadi FK ke tabel SIAKAD. Tabel `siswas`, `user_siswa`, `users`, dan `admin_role*`/`admin_permission` milik ujian-service dibuang.
4. **Otorisasi lewat Policy/Gate**: memakai `User::isAdmin()/isGuru()/isSiswa()` dan `GuruPermission`. Ini menggantikan `jwt.verify`, `exam.role`, `_auth_user`, dan `InjectSiswaAuthContext`.
5. **JSON endpoint hanya untuk interaksi saat mengerjakan ujian** (autosave jawaban, tandai ragu, laporan pelanggaran, submit). Endpoint ini memakai session + CSRF di bawah grup `web`. CRUD lain cukup lewat form Inertia (`router.post`/`useForm`), sehingga controller `Api/*` yang JSON-sentris di-*rewrite*, bukan disalin.
6. **`status` menjadi satu-satunya sumber state publikasi ujian**; kolom `is_published` dibuang.
7. **Realtime proktor ditunda**: tahap awal pakai polling (`router.reload({ only: [...] })` tiap 10 detik). Reverb ditambahkan di fase 7 hanya kalau memang dibutuhkan.

## Fase

Setiap fase menjadi satu commit (atau lebih). Aplikasi harus tetap bisa jalan dan test harus hijau di akhir setiap fase.

### Fase 0: Persiapan
- Amankan perubahan yang belum di-commit di `feat/siswa-independent-login`. Perubahan ini mencakup pekerjaan API mobile `v1` di `routes/api.php` dan `tests/TestCase.php` yang tidak terkait ujian.
- Buat branch `refactor/ujian-modular-monolith`.
- Buat tag `ujian-service-final` sebagai titik rujukan kode lama.

### Fase 1: Infrastruktur Inertia di SIAKAD
- `composer require inertiajs/inertia-laravel tightenco/ziggy`
- `npm i vue @inertiajs/vue3 @vitejs/plugin-vue ziggy-js vuedraggable`
- Konfigurasi `vite.config.js`: tambahkan plugin Vue, alias `@` ke `resources/js`, dan input baru `resources/js/ujian.js`.
- Buat root view `layouts/inertia.blade.php` (shell AdminLTE + `@routes` + `@vite('resources/js/ujian.js')` + `@inertia`).
- Buat `App\Modules\Ujian\UjianServiceProvider` dan daftarkan di `bootstrap/providers.php`.
- Smoke test: route `/ujian` yang merender halaman Inertia kosong untuk user yang login.

### Fase 2: Database dan model
- Squash 24 migration ujian-service menjadi migration `create_*` yang bersih di `database/migrations/ujian/`. Hanya tabel domain ujian yang ikut.
- Buat FK nyata ke `siswas`, `kelas`, `mapel`, dan `gurus`. Buang `is_published`.
- Port model ke `App\Modules\Ujian\Models`. Relasi `siswa()`, `kelas()`, `mapel()`, dan `guru()` menunjuk model SIAKAD. Tambahkan `Siswa::sesiUjian()` di sisi SIAKAD.
- Buat factory dan unit test relasi.

### Fase 3: Otorisasi dan service layer
- Buat Policy dan Gate (lihat keputusan 4).
- Ekstrak logika bisnis dari `Api/SesiController` (698 baris) dan `Api/ProktorController` (849 baris) ke `SesiUjianService`, `PenilaianService`, dan `ProktorService`.
- Buat unit test untuk penilaian, acak soal/opsi, timer/kedaluwarsa, dan pelanggaran.

**Selesai.** Bug ujian-service yang diperbaiki saat ekstraksi (semua ada test-nya):
- `acak_soal` tidak tersimpan (setter `nomor_urut` no-op). Urutan soal dan opsi kini dikunci per siswa di `jawaban_siswa.urutan` / `urutan_opsi`.
- `acak_opsi` mereset key opsi ke 0..n (`collect()->shuffle()->toArray()`), sehingga huruf jawaban rusak, dan diacak ulang tiap reload.
- Bobot essay ikut di penyebut tetapi nilai essay tidak pernah dijumlahkan. `nilai_otomatis`, `nilai_manual`, dan `predikat` tidak pernah diisi.
- `submitAnswer` dengan `soal_id` milik ujian lain membuat baris jawaban baru.
- Paksa selesai oleh proktor tidak menghitung nilai.
- Dua endpoint pelanggaran dengan aturan berbeda (`violation` vs `recordEvent`).
- Status `belum_mulai`/`sedang_mengerjakan` yang tidak ada di enum.
- Otorisasi: `ujian.guru_id` dibandingkan dengan id user, dan 6 endpoint proktor hanya mengecek role.
- Perilaku baru: batas waktu sesi tidak melewati `ujian.waktu_selesai`. Batas pelanggaran bisa dikonfigurasi lewat `UJIAN_BATAS_PELANGGARAN` (0 = nonaktif).

### Fase 4: Fitur guru/admin (Inertia)
- Periode dan fase, bank soal, CRUD ujian dan susun soal, hasil dan koreksi esai, export Excel/PDF.
- Port halaman `Periode/*`, `BankSoal/*`, `Ujian/*`, dan `Hasil/*` ke `resources/js/Ujian/Pages`. Ganti panggilan `axios` + JWT dengan `useForm`/`router`.
- Buat feature test per controller.
- Form periode: input `semester_id` (dropdown semester SIAKAD) menggantikan teks `tahun_ajaran` + enum `semester`.
- `ujian.kelas_id`/`mapel_id` memakai FK `restrict`. Controller hapus Kelas/Mapel di SIAKAD harus menangkap kasus ini dan menampilkan pesan, bukan error 500.

**Selesai.** Catatan:
- Halaman Vue ujian-service selain Dashboard dan Proktor ternyata tidak pernah dirender backend (kontrak field tidak cocok), jadi halaman Fase 4 ditulis baru di atas `useForm` dan markup AdminLTE.
- Peran pengawas: tabel `ujian_pengawas`, ditugaskan admin dari halaman detail ujian. Pengawas boleh melihat dan mengawasi, tetapi tidak boleh mengubah atau menilai.
- Soal terkunci setelah ada sesi siswa. Publikasi butuh minimal 1 soal dan kunci PG yang valid. Kembali ke draft hanya bila belum ada sesi.
- Teks soal dirender sebagai teks biasa (bukan HTML/rich text) untuk menghindari XSS. Rich text + gambar butuh sanitasi server dan upload media, belum dikerjakan.
- Bug SIAKAD yang ikut diperbaiki: sidebar guru dan `GuruDashboardController` masih memanggil `route('guru.ujian.index')` dan `App\Models\SoalUjian` yang sudah dihapus, sehingga semua halaman guru error 500. Menu ujian di sidebar (admin/guru/siswa) kini menunjuk modul ini.
- Untuk Fase 8: `App\Services\ELearningNotifier` punya notifikasi `ujianBaru`, `ujianReminder`, dan `ujianDikoreksi` yang masih memakai model lama. Port ke modul (dipicu saat publikasi dan saat koreksi essay) atau hapus.

### Fase 5: Fitur siswa (Inertia)
- Daftar ujian, mulai sesi, mengerjakan (`Exam/Take.vue`), dan hasil.
- JSON endpoint autosave/ragu/violation/submit (keputusan 5).
- Hapus versi Blade `siswa/sesi/*` dari ujian-service; versi Vue yang dipakai.
- Port `SiswaUjianFlowTest` (alur E2E siswa).

### Fase 6: Proktor
- Dashboard proktor, monitoring sesi, dan aksi (reset, tambah waktu, kunci) dengan polling.

**Fase 5 & 6 selesai.** Diverifikasi di Chromium headless (SQLite terpisah): alur siswa lengkap, autosave + reload, pelanggaran, koreksi essay, monitor proktor dengan polling 10 detik, diskualifikasi yang langsung mengalihkan browser siswa ke halaman hasil.
Belum ada: pengumuman proktor ke peserta (butuh kanal ke siswa; bisa ditumpangkan di respons ping atau Reverb di Fase 7).

### Fase 7 (opsional): Realtime
- Pasang `laravel/reverb` di SIAKAD, port event `SesiUpdated`/`ProktorEvent`, lalu ganti polling dengan Echo.

### Fase 8: Bersih-bersih
- Hapus dari SIAKAD: `UjianSsoController`, `Auth\UjianLoginController`, `Admin\UjianController`, `Admin\HasilUjianController` (setelah export di-port), `SesiUjianObserver` (port notifikasi WA-nya ke modul kalau masih dipakai), view `admin/ujian`, `admin/proktor`, `guru/ujian`, `siswa/ujian`, `auth/ujian-login`, route terkait, `config/services.php` bagian `ujian`, dan env `UJIAN_*`.
- Sidebar: arahkan menu ujian ke route modul dan pulihkan badge "perlu koreksi" lewat model modul.
- Hapus `ujian-service/` dan `setup-integration.sh` jika hanya untuk integrasi.
- Perbarui README, `docs/INSTALL.md`, dan `ENV_CONFIGURATION.md`.

## Yang sengaja tidak dipindah

`JwtTokenService`, `AuthenticateJWT`, `VerifyJwtToken`, `VerifyServiceKey`, `AuthenticateSiswa`, `InjectSiswaAuthContext`, `HandoffController`, `SiswaAuthController`, `SiakadSyncService`, `SiakadIntegrationController`, model `Siswa`/`UserSiswa`/`User` versi ujian-service, tabel `admin_role*`, `layouts/admin*.blade.php` dan partial salinannya, route stub `fn() => 'ok'`, symlink `public/dist` dan `public/plugins`, serta paket `tymon/jwt-auth` dan `firebase/php-jwt`.

## Risiko

- **Logika di controller API besar dan bercampur dengan format respons JSON.** Ada risiko perilaku berubah diam-diam saat di-*rewrite*. Mitigasinya: tulis unit test service di fase 3 sebelum controller lama dibuang.
- **Bentuk props berubah.** Halaman Vue yang sebelumnya fetch data sendiri lewat axios harus diubah menerima props dari Inertia.
- **CSS AdminLTE sama untuk semua halaman**, jadi risiko konflik rendah. Tetap cek `resources/css/app.css` ujian-service untuk style khusus ujian.
