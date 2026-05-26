# SIAKAD SMK — Progress & Implementation Plan

**Stack**: Laravel 11 · AdminLTE 3 (Bootstrap 4) · MySQL
**Role system**: `users.role` — `0` = Admin, `1` = Guru, `2` = Siswa
**Last updated**: 2026-05-26 (Fase 10 selesai — ditambah rencana Fase 11–13)

---

## Status Ringkasan

| Fase | Nama | Status |
|------|------|--------|
| 1 | Setup & Auth | ✅ Selesai |
| 2 | Master Data | ✅ Selesai |
| 3 | Absensi Kelas | ✅ Selesai |
| 4 | Penilaian & Raport | ✅ Selesai |
| 5 | PKL | ✅ Selesai |
| 6 | Kesiswaan | ✅ Selesai |
| 7 | Keuangan / SPP | ✅ Selesai |
| 8 | Notifikasi & Pengumuman | ✅ Selesai |
| 9 | PPDB | ✅ Selesai |
| 10 | Laporan & Integrasi | ✅ Selesai |
| 11 | RBAC Guru (Hak Akses Menu) | ✅ Selesai |
| 12 | Struktur Organisasi Sekolah | ✅ Selesai |
| 13 | Import Excel Master Data | ✅ Selesai |

---

## ✅ Fase 1 — Setup & Auth

**Selesai.**

- Laravel 11 fresh install + AdminLTE 3 layout
- Auth: Breeze (email/password), middleware `role:{n}`
- Model `User` dengan kolom `role` (0/1/2)
- Redirect `/dashboard` berdasarkan role
- Layout: `layouts/admin.blade.php`, `layouts/partials/sidebar.blade.php`, `layouts/partials/navbar.blade.php`

---

## ✅ Fase 2 — Master Data

**Selesai.**

### Yang diimplementasi
- **Jurusan** — CRUD admin (`admin.jurusan.*`)
- **Kelas** — CRUD admin (`admin.kelas.*`), relasi ke Jurusan
- **Mapel** — CRUD admin (`admin.mapel.*`)
- **Guru** — CRUD admin (`admin.guru.*`), relasi ke User
- **Siswa** — CRUD admin (`admin.siswa.*`), relasi ke User + Kelas
- **Jadwal** — CRUD admin (`admin.jadwal.*`), pivot mapel + guru + kelas + hari + jam
- **Tahun Ajaran** — CRUD admin + set aktif (`admin.tahun-ajaran.*`)
- **Semester** — bagian dari TahunAjaran, set aktif (`admin.semester.aktif`)

### Models
`Jurusan`, `Kelas`, `Mapel`, `Guru`, `Siswa`, `Jadwal`, `TahunAjaran`, `Semester`

### Tabel
`jurusans`, `kelas`, `mapels`, `gurus`, `siswas`, `jadwals`, `tahun_ajarans`, `semesters`

---

## ✅ Fase 3 — Absensi Kelas

**Selesai.**

### Yang diimplementasi
- Guru input absensi per jadwal per tanggal (Hadir/Sakit/Izin/Alpha)
- Guru lihat rekap absensi per jadwal
- Admin lihat semua absensi, detail per jadwal
- Siswa lihat rekap absensi diri sendiri

### Models & Tabel
- `Absensi` → `absensis` — kolom: `jadwal_id`, `siswa_id`, `tanggal`, `status` (enum), `keterangan`
- UNIQUE(`jadwal_id`, `siswa_id`, `tanggal`)

### Controllers
- `Guru\AbsensiController` — index, form, simpan (upsert per siswa), rekap
- `Admin\AbsensiController` — index, detail
- `Siswa\AbsensiController` — index (rekap diri sendiri)

### Routes
```
GET  /dosen/absensi                  guru.absensi.index
GET  /dosen/absensi/{jadwal}         guru.absensi.form
POST /dosen/absensi/{jadwal}         guru.absensi.simpan
GET  /dosen/absensi/{jadwal}/rekap   guru.absensi.rekap
GET  /admin/absensi                  admin.absensi.index
GET  /admin/absensi/{jadwal}         admin.absensi.detail
GET  /siswa/absensi                  siswa.absensi
```

---

## ✅ Fase 4 — Penilaian & Raport

**Selesai.**

### Yang diimplementasi
- 6 komponen nilai: `tugas`, `pts`, `pas`, `praktik`, `proyek`, `portofolio`
- Computed: `rata_pengetahuan` (avg tugas+pts+pas), `rata_keterampilan` (avg praktik+proyek+portofolio), `nilai_akhir` (avg keduanya), `predikat` (A/B/C/D)
- Guru input nilai per jadwal per siswa (batch update)
- Guru lihat rekap nilai per jadwal
- Admin lihat progress pengisian (X/Y siswa dinilai) per jadwal
- Admin lihat detail nilai per jadwal
- Admin cetak raport (pilih kelas → pilih siswa → tampil semua mapel)
- Siswa lihat nilai per mapel + raport mandiri
- `@media print` CSS untuk cetak raport

### Models & Tabel
- `Nilai` → `nilais` — kolom: `jadwal_id`, `siswa_id`, 6 decimal(5,2) nullable, `catatan`, `dicatat_oleh`
- UNIQUE(`jadwal_id`, `siswa_id`)
- Accessors: `rata_pengetahuan`, `rata_keterampilan`, `nilai_akhir`, `predikat`, `predikat_color`

### Controllers
- `Guru\NilaiController` — index, form, simpan (updateOrCreate per siswa), rekap
- `Admin\NilaiController` — index (dengan jumlah_siswa/jumlah_dinilai), detail, raport
- `Siswa\NilaiController` — index, raport

### Routes
```
GET  /admin/nilai                  admin.nilai.index
GET  /admin/nilai/{jadwal}         admin.nilai.detail
GET  /admin/raport                 admin.raport
GET  /dosen/nilai                  guru.nilai.index
GET  /dosen/nilai/{jadwal}         guru.nilai.form
POST /dosen/nilai/{jadwal}         guru.nilai.simpan
GET  /dosen/nilai/{jadwal}/rekap   guru.nilai.rekap
GET  /siswa/nilai                  siswa.nilai
GET  /siswa/raport                 siswa.raport
```

---

## ✅ Fase 5 — PKL (Praktek Kerja Lapangan)

**Selesai.**

### Yang diimplementasi
- CRUD Tempat PKL (nama, alamat, bidang usaha, kontak, kuota)
- CRUD Penempatan siswa ke tempat PKL (assign guru pembimbing, periode, status aktif/selesai)
- Jurnal harian siswa (tanggal, kegiatan, foto upload, keterangan)
- Absensi PKL: **geolocation (GPS) + foto kamera browser** — jam masuk & pulang terpisah
- Penilaian PKL dari guru pembimbing: nilai_pembimbing, nilai_du_di, nilai_sikap → nilai_akhir + predikat
- Admin: overview stats, detail per penempatan (jurnal + absensi + nilai)
- Guru: list bimbingan, detail siswa, form input nilai
- Siswa: info penempatan, kelola jurnal, absensi masuk/pulang

### Models & Tabel
| Model | Tabel | Unique Constraint |
|-------|-------|-------------------|
| `TempatPkl` | `tempat_pkls` | `nama` |
| `PenempatanPkl` | `penempatan_pkls` | `(siswa_id, semester_id)` |
| `JurnalPkl` | `jurnal_pkls` | `(penempatan_pkl_id, tanggal)` |
| `AbsensiPkl` | `absensi_pkls` | `(penempatan_pkl_id, tanggal)` |
| `NilaiPkl` | `nilai_pkls` | `penempatan_pkl_id` |

### Teknis Absensi (geolocation + foto)
- Browser: `navigator.geolocation.getCurrentPosition()` → lat/lng
- Browser: `navigator.mediaDevices.getUserMedia({video: true})` → `<video>` → capture ke `<canvas>` → `toDataURL('image/jpeg')` → base64
- Submit via `fetch()` JSON POST dengan `X-CSRF-TOKEN`
- Backend: `base64_decode()` → `Storage::disk('public')->put('pkl/foto/...', $content)`
- Tampil: `asset('storage/' . $record->foto_masuk)`

### Controllers
- `Admin\TempatPklController` — resource CRUD (param: `pklTempat`)
- `Admin\PenempatanPklController` — resource CRUD (param: `pklPenempatan`)
- `Admin\PklController` — index, detail
- `Guru\PklController` — index, detail, nilaiForm, nilaiSimpan
- `Siswa\PklController` — index, jurnal, jurnalStore, absensi, absensiMasuk, absensiPulang

### Routes
```
GET  /admin/pkl                          admin.pkl.index
GET  /admin/pkl/{penempatan}             admin.pkl.detail
resource /admin/pkl/tempat               admin.pkl-tempat.*
resource /admin/pkl/penempatan           admin.pkl-penempatan.*
GET  /dosen/pkl                          guru.pkl.index
GET  /dosen/pkl/{penempatan}             guru.pkl.detail
GET  /dosen/pkl/{penempatan}/nilai       guru.pkl.nilai
POST /dosen/pkl/{penempatan}/nilai       guru.pkl.nilai.simpan
GET  /siswa/pkl                          siswa.pkl
GET  /siswa/pkl/jurnal                   siswa.pkl.jurnal
POST /siswa/pkl/jurnal                   siswa.pkl.jurnal.store
GET  /siswa/pkl/absensi                  siswa.pkl.absensi
POST /siswa/pkl/absensi/masuk            siswa.pkl.absensi.masuk
POST /siswa/pkl/absensi/pulang           siswa.pkl.absensi.pulang
```

---

## ✅ Fase 6 — Kesiswaan

**Selesai.**

### Yang diimplementasi
- **Pelanggaran Siswa** — CRUD admin: catat pelanggaran, jenis, poin, deskripsi, akumulasi poin per siswa
- **Prestasi Siswa** — CRUD admin: nama prestasi, tingkat (Sekolah s.d. Internasional), upload foto bukti
- **Ekstrakurikuler** — CRUD admin: kelola ekskul + pembina guru + pendaftaran anggota per tahun ajaran
- **Absensi Ekskul** — Guru pembina input absensi ekskul per tanggal (Hadir/Izin/Sakit/Alpa) + rekap kehadiran grid
- **Surat Keterangan** — Admin generate surat aktif/pindah/lulus: kop sekolah, nomor surat, print-friendly (`@media print`)
- **Rekap Siswa** — Siswa lihat rekap pelanggaran (total poin), prestasi (foto bukti), ekskul + persentase kehadiran

### Models & Tabel
| Model | Tabel | Unique Constraint |
|-------|-------|-------------------|
| `Pelanggaran` | `pelanggarans` | — |
| `Prestasi` | `prestasis` | — |
| `Ekskul` | `ekskuls` | `nama` |
| `AnggotaEkskul` | `anggota_ekskuls` | `(ekskul_id, siswa_id, tahun_ajaran_id)` |
| `AbsensiEkskul` | `absensi_ekskuls` | `(ekskul_id, siswa_id, tanggal)` |
| `SuratKeterangan` | `surat_keterangans` | — |

### Controllers
- `Admin\PelanggaranController` — resource CRUD
- `Admin\PrestasiController` — resource CRUD + upload foto
- `Admin\EkskulController` — resource CRUD + `anggota()`, `anggotaStore()`, `anggotaDestroy()`
- `Admin\SuratController` — resource CRUD + `print()`
- `Guru\EkskulController` — `index`, `detail`, `absensiForm`, `absensiStore`, `absensiRekap`
- `Siswa\KesiswaanController` — `index` (rekap pelanggaran, prestasi, ekskul)

### Routes
```
GET  /admin/pelanggaran              admin.pelanggaran.index
POST /admin/pelanggaran              admin.pelanggaran.store
GET  /admin/pelanggaran/create       admin.pelanggaran.create
GET  /admin/pelanggaran/{p}/edit     admin.pelanggaran.edit
DEL  /admin/pelanggaran/{p}          admin.pelanggaran.destroy
GET  /admin/prestasi                 admin.prestasi.index
POST /admin/prestasi                 admin.prestasi.store (multipart, foto)
GET  /admin/prestasi/create          admin.prestasi.create
GET  /admin/prestasi/{p}/edit        admin.prestasi.edit
DEL  /admin/prestasi/{p}             admin.prestasi.destroy
GET  /admin/ekskul                   admin.ekskul.index
POST /admin/ekskul                   admin.ekskul.store
GET  /admin/ekskul/{e}/anggota       admin.ekskul.anggota
POST /admin/ekskul/{e}/anggota       admin.ekskul.anggota.store
DEL  /admin/ekskul/{e}/anggota/{a}   admin.ekskul.anggota.destroy
GET  /admin/surat                    admin.surat.index
POST /admin/surat                    admin.surat.store
GET  /admin/surat/{s}/print          admin.surat.print
GET  /dosen/ekskul                   guru.ekskul.index
GET  /dosen/ekskul/{e}               guru.ekskul.detail
GET  /dosen/ekskul/{e}/absensi       guru.ekskul.absensi
POST /dosen/ekskul/{e}/absensi       guru.ekskul.absensi.store
GET  /dosen/ekskul/{e}/rekap         guru.ekskul.rekap
GET  /siswa/kesiswaan                siswa.kesiswaan
```

---

## ⬜ Fase 7 — Keuangan / SPP

**Belum dimulai.**

### Rencana

#### Fitur
1. **Tagihan SPP** — generate tagihan bulanan per siswa per kelas
2. **Pembayaran** — catat pembayaran, upload bukti
3. **Laporan keuangan** — rekap per bulan, per kelas, tunggakan

#### Models & Tabel (rencana)
| Model | Tabel | Kolom Utama |
|-------|-------|-------------|
| `TagihanSpp` | `tagihan_spps` | siswa_id, bulan (date), nominal, status (enum: lunas/belum/cicil) |
| `PembayaranSpp` | `pembayaran_spps` | tagihan_spp_id, tanggal_bayar, nominal_bayar, metode, bukti (file), dicatat_oleh |
| `TarifSpp` | `tarif_spps` | kelas_id, tahun_ajaran_id, nominal — UNIQUE(kelas_id, tahun_ajaran_id) |

#### Controllers (rencana)
- `Admin\TagihanSppController` — generate batch per kelas, CRUD
- `Admin\PembayaranSppController` — input pembayaran, upload bukti
- `Admin\LaporanKeuanganController` — rekap tunggakan, export
- `Siswa\SppController` — lihat tagihan + riwayat pembayaran diri sendiri

---

## ⬜ Fase 8 — Notifikasi & Pengumuman

**Belum dimulai.**

### Rencana

#### Fitur
1. **Pengumuman** — admin buat pengumuman, target: semua/guru/siswa/kelas tertentu
2. **Notifikasi in-app** — bell icon di navbar, unread count, mark as read
3. **(Opsional)** Push notifikasi via browser (Web Push API)

#### Models & Tabel (rencana)
| Model | Tabel | Kolom Utama |
|-------|-------|-------------|
| `Pengumuman` | `pengumumans` | judul, isi (text), target_role (nullable), target_kelas_id (nullable), dibuat_oleh, published_at |
| `Notifikasi` | `notifikasis` | user_id, judul, pesan, url (nullable), read_at (nullable) |

#### Controllers (rencana)
- `Admin\PengumumanController` — CRUD, publish
- `NotifikasiController` — index, markRead, markAllRead (API endpoint AJAX)

---

## ⬜ Fase 9 — PPDB (Penerimaan Peserta Didik Baru)

**Belum dimulai.**

### Rencana

#### Fitur
1. **Formulir pendaftaran** — publik (tanpa login), isi data calon siswa + upload dokumen
2. **Seleksi** — admin review, approve/reject, set kelas
3. **Aktivasi akun** — generate akun siswa otomatis setelah diterima
4. **Laporan PPDB** — jumlah pendaftar, diterima, ditolak per jurusan

#### Models & Tabel (rencana)
| Model | Tabel | Kolom Utama |
|-------|-------|-------------|
| `PendaftarPpdb` | `pendaftar_ppdbs` | nama, email, no_hp, alamat, asal_sekolah, jurusan_pilihan_id, dokumen (JSON/nullable), status (enum: pending/diterima/ditolak), catatan_admin |

#### Controllers (rencana)
- `PpdbController` — form pendaftaran publik, store, status check (by no. pendaftaran)
- `Admin\PpdbController` — index pendaftar, review, approve (create Siswa + User), reject, laporan

---

## ⬜ Fase 10 — Laporan & Integrasi

**Belum dimulai.**

### Rencana

#### Fitur
1. **Export Excel** — nilai, absensi, SPP (via `maatwebsite/excel`)
2. **Export PDF** — raport resmi, surat keterangan (via `barryvdh/laravel-dompdf`)
3. **Dashboard analytics** — chart rekapitulasi (Chart.js): absensi per bulan, distribusi nilai, SPP tunggakan
4. **API mobile** (opsional) — endpoint JSON untuk siswa (nilai, jadwal, absensi)
5. **Backup otomatis** — scheduled command `php artisan backup:run` (via `spatie/laravel-backup`)

#### Dependencies (rencana)
```bash
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
composer require spatie/laravel-backup  # opsional
```

---

## Struktur Direktori Saat Ini

```
app/
  Http/
    Controllers/
      Admin/
        AbsensiController.php
        GuruController.php
        JadwalController.php
        JurusanController.php
        KelasController.php
        MapelController.php
        NilaiController.php          ← Fase 4
        PenempatanPklController.php  ← Fase 5
        PklController.php            ← Fase 5
        SiswaAdminController.php
        TahunAjaranController.php
        TempatPklController.php      ← Fase 5
      Guru/
        AbsensiController.php
        NilaiController.php          ← Fase 4
        PklController.php            ← Fase 5
      Siswa/
        AbsensiController.php
        NilaiController.php          ← Fase 4
        PklController.php            ← Fase 5
      AdminController.php
      DosenController.php
      ProfileController.php
      SiswaController.php
  Models/
    AbsensiPkl.php    ← Fase 5
    Guru.php
    Jadwal.php
    JurnalPkl.php     ← Fase 5
    Kelas.php
    Jurusan.php
    Mapel.php
    Nilai.php         ← Fase 4
    NilaiPkl.php      ← Fase 5
    PenempatanPkl.php ← Fase 5
    Semester.php
    Siswa.php
    TahunAjaran.php
    TempatPkl.php     ← Fase 5
    User.php
resources/
  views/
    admin/
      absensi/
      guru/
      jadwal/
      jurusan/
      kelas/
      mapel/
      nilai/           ← Fase 4
      pkl/             ← Fase 5
        tempat/
        penempatan/
        index.blade.php
        detail.blade.php
      siswa/
      tahun-ajaran/
    guru/
      absensi/
      nilai/           ← Fase 4
      pkl/             ← Fase 5
    siswa/
      absensi.blade.php
      jadwal.blade.php
      mapel.blade.php
      nilai.blade.php  ← Fase 4
      raport.blade.php ← Fase 4
      pkl/             ← Fase 5
    layouts/
      admin.blade.php
      partials/
        sidebar.blade.php
        navbar.blade.php
database/
  migrations/
    ...semua migration sudah di-migrate
```

---

## Catatan Teknis

### Pattern yang digunakan
- **updateOrCreate** — untuk upsert nilai dan absensi (idempoten)
- **Route model binding** — otomatis resolve model dari ID di URL
- **`keyBy('siswa_id')`** — untuk mapping koleksi ke array asosiatif di view nilai
- **Base64 foto** — absensi PKL: canvas → base64 → JSON POST → `base64_decode` → `Storage::put`
- **`@media print`** — CSS khusus cetak untuk raport
- **Middleware `role:n`** — proteksi route per peran

### Konvensi nama route
- Admin: prefix `admin.` — e.g. `admin.siswa.index`
- Guru: `guru.*` — e.g. `guru.nilai.index`
- Siswa: `siswa.*` — e.g. `siswa.pkl`
- PKL resource admin: `admin.pkl-tempat.*`, `admin.pkl-penempatan.*` (bukan default karena nested path)

### Storage
- Foto jurnal PKL: `storage/app/public/pkl/jurnal/`
- Foto absensi PKL: `storage/app/public/pkl/foto/`
- Akses publik via: `asset('storage/...')`
- Jalankan `php artisan storage:link` jika belum

---

## ✅ Fase 11 — RBAC Guru (Hak Akses Menu)

**Selesai.**

### Latar Belakang

Saat ini semua guru mendapat akses ke seluruh menu guru (absensi, nilai, PKL, ekskul, dll). Fase ini menambahkan **granular permission per guru** — admin bisa mengatur menu apa saja yang boleh diakses masing-masing guru.

### Daftar Menu yang Dikontrol

| Key Permission | Label Menu | Route Terlibat |
|----------------|-----------|----------------|
| `menu_jadwal` | Jadwal Mengajar | `guru.jadwal` |
| `menu_mapel` | Mata Pelajaran | `guru.mapel` |
| `menu_absensi` | Input Absensi Kelas | `guru.absensi.*` |
| `menu_nilai` | Input Nilai | `guru.nilai.*` |
| `menu_pkl` | PKL Bimbingan | `guru.pkl.*` |
| `menu_ekskul` | Ekskul Binaan | `guru.ekskul.*` |

### Model & Tabel

#### `guru_permissions` (tabel baru)
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | bigint PK | |
| `guru_id` | FK → gurus | UNIQUE (satu record per guru) |
| `menu_jadwal` | boolean default true | |
| `menu_mapel` | boolean default true | |
| `menu_absensi` | boolean default true | |
| `menu_nilai` | boolean default true | |
| `menu_pkl` | boolean default true | |
| `menu_ekskul` | boolean default true | |
| timestamps | | |

> Default semua `true` agar guru lama tidak kehilangan akses saat fase ini di-deploy.

Model `GuruPermission`:
- `belongsTo(Guru::class)`
- Accessor `getAllPermissionsAttribute()` → array key-value

Model `Guru` tambahan:
- `hasOne(GuruPermission::class)`
- Helper `canAccess(string $menu): bool` → cek permission atau default true jika belum ada record

### Middleware Baru

`CheckGuruPermission` — diregistrasi sebagai `guru.can`:
```
Route::get('/dosen/absensi', ...)->middleware(['role:1', 'guru.can:menu_absensi']);
```
- Ambil `auth()->user()->guru->permission`
- Jika kolom permission = false → abort(403) atau redirect dengan flash error

### Controller & View

**`Admin\GuruPermissionController`**
- `edit(Guru $guru)` — form checklist permission
- `update(Guru $guru)` — simpan dengan `updateOrCreate`

View `admin/guru/permission.blade.php`:
- Daftar checklist toggle (Bootstrap switch) per menu
- Terintegrasi di halaman detail / edit guru

### Route Baru
```
GET  /admin/guru/{guru}/permission       admin.guru.permission.edit
PUT  /admin/guru/{guru}/permission       admin.guru.permission.update
```

### Sidebar Guru

`sidebar.blade.php` — setiap item menu guru dibungkus kondisi:
```blade
@if(auth()->user()->guru?->canAccess('menu_absensi'))
  <li class="nav-item">...</li>
@endif
```

### Langkah Implementasi

1. Migration `create_guru_permissions_table`
2. Model `GuruPermission` + update model `Guru`
3. Middleware `CheckGuruPermission` + register di `bootstrap/app.php`
4. `Admin\GuruPermissionController` (edit + update)
5. View `admin/guru/permission.blade.php`
6. Update sidebar.blade.php — kondisi per menu guru
7. Tambah middleware ke routes guru di web.php
8. Seeder default: generate `GuruPermission` untuk semua guru existing

---

## ✅ Fase 12 — Struktur Organisasi Sekolah

**Selesai.**

### Fitur

1. **Admin CRUD** — kelola jabatan + assign guru/staf per jabatan
2. **Tampilan visual** — bagan hierarki (CSS tree atau Bootstrap card grid)
3. **Halaman publik** — tampil di website sekolah (`/organisasi`)
4. **Tampilan internal** — menu di sidebar admin & siswa (opsional)

### Model & Tabel

#### `jabatan_organisasis`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | bigint PK | |
| `nama_jabatan` | varchar 150 | Kepala Sekolah, Waka Kurikulum, dst |
| `parent_id` | FK nullable → self | Hierarki (null = puncak) |
| `urutan` | int default 0 | Urutan tampil dalam level yang sama |
| `deskripsi` | text nullable | |
| timestamps | | |

#### `anggota_organisasis`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `id` | bigint PK | |
| `jabatan_organisasi_id` | FK | |
| `nama` | varchar 150 | Nama bisa guru internal atau tamu |
| `guru_id` | FK nullable → gurus | Jika diisi, nama & foto diambil dari guru |
| `foto` | varchar nullable | Upload foto jika bukan guru internal |
| `nip` | varchar nullable | |
| `periode` | varchar 50 nullable | e.g. "2024–2027" |
| `is_aktif` | boolean default true | |
| timestamps | | |

### Tampilan Hierarki

Gunakan **CSS flexbox tree** (tidak perlu library tambahan):
- Level 0 (root): Kepala Sekolah — center
- Level 1: Waka-waka — row di bawah
- Level 2: Kaproli, BK, dll — row di bawah masing-masing waka
- Garis penghubung via CSS border/pseudo-element

Alternatif ringan: card grid per level dengan `indikator level` tanpa garis (lebih mobile-friendly).

### Controllers

- `Admin\StrukturOrganisasiController` — resource CRUD jabatan + endpoint kelola anggota
- `LandingController` (update) — tambah method `organisasi()` untuk halaman publik

### Route Baru
```
GET    /admin/organisasi                     admin.organisasi.index
GET    /admin/organisasi/create              admin.organisasi.create
POST   /admin/organisasi                     admin.organisasi.store
GET    /admin/organisasi/{jabatan}/edit      admin.organisasi.edit
PUT    /admin/organisasi/{jabatan}           admin.organisasi.update
DEL    /admin/organisasi/{jabatan}           admin.organisasi.destroy
GET    /admin/organisasi/{jabatan}/anggota   admin.organisasi.anggota
POST   /admin/organisasi/{jabatan}/anggota   admin.organisasi.anggota.store
DEL    /admin/organisasi/{jabatan}/anggota/{a} admin.organisasi.anggota.destroy

GET    /organisasi                           public.organisasi  (publik, no auth)
```

### Views
```
resources/views/admin/organisasi/
  index.blade.php       ← daftar jabatan + tree preview
  form.blade.php        ← create/edit jabatan
  anggota.blade.php     ← kelola anggota jabatan
resources/views/public/
  organisasi.blade.php  ← tampilan publik bagan organisasi
```

### Langkah Implementasi

1. Migration `create_jabatan_organisasis_table` + `create_anggota_organisasis_table`
2. Model `JabatanOrganisasi` (self-referential `parent`/`children`) + `AnggotaOrganisasi`
3. `Admin\StrukturOrganisasiController` — full CRUD
4. Views admin (index + form + anggota)
5. Update `LandingController` → method `organisasi()`
6. View publik `public/organisasi.blade.php` + CSS tree
7. Tambah link ke menu sidebar admin & navbar publik
8. Tambah route ke web.php

---

## ✅ Fase 13 — Import Excel Master Data

**Selesai.**

### Dependensi

`maatwebsite/excel ^3.1` **sudah terpasang** di `composer.json` ✅ — tidak perlu install tambahan.

### Cakupan Import

| Data | Sheet/File | Aksi setelah import |
|------|-----------|---------------------|
| **Guru** | Template: `template_guru.xlsx` | Buat `User` (role=1) + `Guru`, generate password dari NIP |
| **Siswa** | Template: `template_siswa.xlsx` | Buat `User` (role=2) + `Siswa`, assign kelas jika ada |
| **Mata Pelajaran** | Template: `template_mapel.xlsx` | Insert/update `Mapel` |
| **Tagihan SPP** | Template: `template_tagihan_spp.xlsx` | Generate batch `TagihanSpp` per siswa per bulan |

### Template Excel (format kolom)

#### Guru (`template_guru.xlsx`)
```
NIP | Nama Lengkap | Bidang Studi | No HP | Email (opsional)
```
- Password default: NIP (di-hash)
- Username default: NIP
- Jika NIP sudah ada → skip (tidak overwrite)

#### Siswa (`template_siswa.xlsx`)
```
NIS | Nama Lengkap | Kelas (nama) | No HP | Alamat | Tanggal Lahir | Email (opsional)
```
- Password default: NIS (di-hash)
- Username default: NIS
- Kolom `Kelas` dicocokkan ke tabel `kelas.nama` — jika tidak ketemu, siswa tetap dibuat tanpa kelas

#### Mata Pelajaran (`template_mapel.xlsx`)
```
Kode Mapel | Nama Mapel | Jurusan (nama) | Deskripsi
```
- Upsert berdasarkan `kode` — jika kode sudah ada, nama/deskripsi diperbarui

#### Tagihan SPP (`template_tagihan_spp.xlsx`)
```
NIS | Bulan (YYYY-MM) | Nominal | Keterangan
```
- Lookup siswa berdasarkan NIS
- `updateOrCreate` berdasarkan `(siswa_id, bulan)` agar idempoten

### Import Classes (app/Imports/)

```
app/Imports/
  GuruImport.php         ← implements ToCollection, WithHeadingRow, WithValidation
  SiswaImport.php
  MapelImport.php
  TagihanSppImport.php
```

Setiap class:
- `WithHeadingRow` — baris pertama = header (case-insensitive slug)
- `WithValidation` — validasi kolom wajib
- Return `ImportResult` (baris berhasil, baris gagal + alasan) — ditampilkan di view sebagai summary

### Controllers

**`Admin\ImportController`**
- `index()` — halaman pilih jenis import + form upload
- `template(string $type)` — download file template `.xlsx`
- `store(Request $request)` — proses file + return hasil

### Route Baru
```
GET  /admin/import              admin.import.index
GET  /admin/import/template/{type}  admin.import.template   (download)
POST /admin/import              admin.import.store
```

### View
```
resources/views/admin/import/
  index.blade.php   ← tab/accordion per jenis data + form upload + tabel hasil
```

UI per tab:
1. Tombol **Download Template** → GET `/admin/import/template/guru`
2. Form upload file `.xlsx`
3. Setelah submit: tabel dua bagian — **Berhasil** (N baris) | **Gagal** (baris + alasan)

### Langkah Implementasi

1. Buat `app/Imports/GuruImport.php` + `SiswaImport.php` + `MapelImport.php` + `TagihanSppImport.php`
2. Buat file template Excel di `storage/app/templates/` (bisa di-generate via seeder atau diletakkan sebagai asset statis)
3. `Admin\ImportController` — index, template download, store
4. View `admin/import/index.blade.php`
5. Tambah route ke web.php
6. Tambah link menu di sidebar admin (bagian Konfigurasi atau Data Master)

### Catatan Keamanan
- Validasi ekstensi file: hanya `.xlsx` / `.xls`
- Batas ukuran file: 2MB (konfigurasi `php.ini` / `upload_max_filesize`)
- Wrap seluruh proses import dalam `DB::transaction()` per baris agar gagal parsial tidak merusak data
