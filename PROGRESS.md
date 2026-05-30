# SIAKAD SMK — Progress & Implementation Plan

**Stack**: Laravel 11 · AdminLTE 3 (Bootstrap 4) · MySQL
**Role system**: `users.role` — `0` = Admin, `1` = Guru, `2` = Siswa, `3` = Pendaftar PPDB
**Last updated**: 2026-05-28 (Semua fase selesai, ditambah dokumentasi fitur baru)

---

## Status Ringkasan

| Fase | Nama | Status |
|------|------|--------|
| 1 | Setup & Auth | ✅ Selesai |
| 2 | Master Data | ✅ Selesai |
| 3 | Absensi Kelas | ✅ Selesai |
| 4 | Penilaian & Raport | ✅ Selesai |
| 5 | PKL (Praktek Kerja Lapangan) | ✅ Selesai |
| 6 | Kesiswaan | ✅ Selesai |
| 7 | Keuangan / SPP & Midtrans | ✅ Selesai |
| 8 | Notifikasi & Pengumuman | ✅ Selesai |
| 9 | PPDB & PPDB Plus | ✅ Selesai |
| 10 | Laporan & Integrasi | ✅ Selesai |
| 11 | RBAC Guru (Hak Akses Menu) | ✅ Selesai |
| 12 | Struktur Organisasi Sekolah | ✅ Selesai |
| 13 | Import Excel Master Data | ✅ Selesai |
| 14 | E-Learning (Materi & Tugas) | ✅ Selesai |
| 15 | Website CMS & Landing Page | ✅ Selesai |
| 16 | Integrasi WhatsApp & Kontak | ✅ Selesai |
| 17 | API Mobile | ✅ Selesai |

---

## ✅ Fase 1 — Setup & Auth

**Selesai.**

- Laravel 11 fresh install + AdminLTE 3 layout
- Auth: Breeze (email/username/password), middleware `role:{n}`
- Model `User` dengan kolom `role` (0 = Admin, 1 = Guru, 2 = Siswa, 3 = Pendaftar)
- Redirect `/dashboard` berdasarkan role
- Layout: `layouts/admin.blade.php`, `layouts/partials/sidebar.blade.php`, `layouts/partials/navbar.blade.php`

---

## ✅ Fase 2 — Master Data

**Selesai.**

### Yang diimplementasi
- **Jurusan** — CRUD admin (`admin.jurusan.*`)
- **Kelas** — CRUD admin (`admin.kelas.*`), relasi ke Jurusan
- **Mapel** — CRUD admin (`admin.mapel.*`), migrasi `create_matkuls_table` (membuat tabel `mapels`)
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
GET  /guru/absensi                  guru.absensi.index
GET  /guru/absensi/{jadwal}         guru.absensi.form
POST /guru/absensi/{jadwal}         guru.absensi.simpan
GET  /guru/absensi/{jadwal}/rekap   guru.absensi.rekap
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
- Admin cetak raport (pilih kelas → pilih siswa → tampil semua mapel) dengan support export PDF
- Siswa lihat nilai per mapel + raport mandiri
- `@media print` CSS dan dompdf untuk cetak raport

### Models & Tabel
- `Nilai` → `nilais` — kolom: `jadwal_id`, `siswa_id`, 6 decimal(5,2) nullable, `catatan`, `dicatat_oleh`
- UNIQUE(`jadwal_id`, `siswa_id`)
- Accessors: `rata_pengetahuan`, `rata_keterampilan`, `nilai_akhir`, `predikat`, `predikat_color`

### Controllers
- `Guru\NilaiController` — index, form, simpan (updateOrCreate per siswa), rekap
- `Admin\NilaiController` — index, detail, raport, raportPdf
- `Siswa\NilaiController` — index, raport

### Routes
```
GET  /admin/nilai                  admin.nilai.index
GET  /admin/nilai/{jadwal}         admin.nilai.detail
GET  /admin/raport                 admin.raport
GET  /admin/raport/pdf             admin.raport.pdf
GET  /guru/nilai                  guru.nilai.index
GET  /guru/nilai/{jadwal}         guru.nilai.form
POST /guru/nilai/{jadwal}         guru.nilai.simpan
GET  /guru/nilai/{jadwal}/rekap   guru.nilai.rekap
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
GET  /guru/pkl                          guru.pkl.index
GET  /guru/pkl/{penempatan}             guru.pkl.detail
GET  /guru/pkl/{penempatan}/nilai       guru.pkl.nilai
POST /guru/pkl/{penempatan}/nilai       guru.pkl.nilai.simpan
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
POST /admin/prestasi                 admin.prestasi.store
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
GET  /guru/ekskul                   guru.ekskul.index
GET  /guru/ekskul/{e}               guru.ekskul.detail
GET  /guru/ekskul/{e}/absensi       guru.ekskul.absensi
POST /guru/ekskul/{e}/absensi       guru.ekskul.absensi.store
GET  /guru/ekskul/{e}/rekap         guru.ekskul.rekap
GET  /siswa/kesiswaan                siswa.kesiswaan
```

---

## ✅ Fase 7 — Keuangan / SPP & Midtrans

**Selesai.**

### Yang diimplementasi
- **Tarif SPP** — nominal SPP per kelas per tahun ajaran
- **Tagihan SPP** — auto-generate tagihan bulanan per siswa berdasarkan kelas
- **Pembayaran SPP Manual** — pencatatan tunai, transfer, QRIS oleh admin/siswa dengan upload bukti transfer
- **Konfirmasi Admin** — admin memverifikasi dan menyetujui bukti pembayaran manual siswa
- **Laporan Keuangan** — rekap bulanan penerimaan kas, rekap tunggakan per kelas, export Excel, dan cetak kwitansi pembayaran
- **Integrasi Midtrans Gateway** — pembayaran online real-time via Snap Token (Siswa) + Webhook auto-confirm (Midtrans Callback)

### Models & Tabel
| Model | Tabel | Kolom Utama / Relasi |
|-------|-------|-------------------|
| `TarifSpp` | `tarif_spps` | `kelas_id`, `tahun_ajaran_id`, `nominal` (UNIQUE) |
| `TagihanSpp` | `tagihan_spps` | `siswa_id`, `bulan` (date), `nominal`, `status` (lunas, belum, cicil), `dicatat_oleh` |
| `PembayaranSpp` | `pembayaran_spps` | `tagihan_spp_id`, `tanggal_bayar`, `nominal_bayar`, `metode`, `bukti`, `status_konfirmasi` (pending, dikonfirmasi, ditolak), `midtrans_order_id`, `midtrans_transaction_id` |

### Controllers & Services
- `Admin\TarifSppController` — CRUD tarif
- `Admin\TagihanSppController` — CRUD tagihan, batch generate, export Excel
- `Admin\PembayaranSppController` — Input pembayaran manual, konfirmasi pembayaran, hapus pembayaran
- `Admin\LaporanKeuanganController` — Rekap bulanan, daftar tunggakan, print kwitansi
- `Siswa\SppController` — List tagihan diri, upload bukti bayar, request Midtrans snap-token
- `MidtransWebhookController` — Handler callback transaksi otomatis dari Midtrans
- `App\Services\MidtransService` — Wrapper SDK/API Midtrans untuk integrasi Snap

### Routes
```
GET  /admin/keuangan/tarif                  admin.keuangan.tarif.index
POST /admin/keuangan/tagihan/{t}/bayar       admin.keuangan.tagihan.bayar
POST /admin/keuangan/pembayaran/{p}/konfirmasi admin.keuangan.pembayaran.konfirmasi
GET  /admin/keuangan/laporan                admin.keuangan.laporan
GET  /admin/keuangan/tunggakan              admin.keuangan.tunggakan
GET  /admin/keuangan/kwitansi/{p}           admin.keuangan.kwitansi
GET  /siswa/spp                             siswa.spp
POST /siswa/spp/{tagihan}/upload            siswa.spp.upload
POST /siswa/spp/{tagihan}/snap-token        siswa.spp.snap-token
POST /webhook/midtrans                      webhook.midtrans
```

---

## ✅ Fase 8 — Notifikasi & Pengumuman

**Selesai.**

### Yang diimplementasi
- **Pengumuman** — Pembuatan pengumuman oleh admin dengan target role tertentu (Semua, Guru, Siswa) dan filter kelas tertentu
- **Notifikasi In-App** — Pengiriman notifikasi internal ke user penerima setelah pengumuman dipublish
- **Mark as Read** — Icon lonceng dengan unread count, fitur baca/tandai semua dibaca untuk user

### Models & Tabel
| Model | Tabel | Kolom Utama |
|-------|-------|-------------|
| `Pengumuman` | `pengumumans` | `judul`, `isi` (text), `target_role`, `target_kelas_id`, `dibuat_oleh`, `published_at` |
| `Notifikasi` | `notifikasis` | `user_id`, `judul`, `pesan`, `url`, `read_at` |

### Controllers
- `Admin\PengumumanController` — CRUD pengumuman, method `publish` + broadcast trigger
- `PengumumanController` — List pengumuman publik/internal setelah login
- `NotifikasiController` — List notifikasi user, `markRead`, `markAllRead`

### Routes
```
GET  /admin/pengumuman                      admin.pengumuman.index
POST /admin/pengumuman/{pengumuman}/publish admin.pengumuman.publish
GET  /pengumuman                            pengumuman.index
GET  /pengumuman/{pengumuman}               pengumuman.show
GET  /notifikasi                            notifikasi.index
POST /notifikasi/{notifikasi}/read          notifikasi.read
POST /notifikasi/read-all                   notifikasi.readAll
```

---

## ✅ Fase 9 — PPDB & PPDB Plus

**Selesai.**

### Yang diimplementasi
- **Form Pendaftaran Publik** — Pendaftaran tanpa login: isi data diri, ortu, nilai rata-rata, pilihan jurusan 1 & 2
- **Portal Pendaftar (Role 3)** — Setelah register, pendaftar mendapat akun (role=3) untuk upload berkas (Foto, Ijazah, KK, Akta, Rapor)
- **Gelombang PPDB** — Batasan pendaftaran berdasarkan rentang tanggal aktif dan kuota pendaftar per gelombang
- **Seleksi & Aktivasi Akun** — Admin memproses status pendaftar (Verifikasi/Terima/Tolak). Jika diterima, otomatis upgrade role ke Siswa (role=2) dan generate akun kelas/NISN
- **FAQ & Persyaratan** — Manajemen dinamis informasi persyaratan pendaftaran dan tanya jawab PPDB di halaman landing

### Models & Tabel
| Model | Tabel | Kolom Utama |
|-------|-------|-------------|
| `PendaftarPpdb` | `pendaftar_ppdbs` | `user_id`, `no_pendaftaran`, `nama`, `nisn`, `status` (pending, verifikasi, diterima, ditolak), `gelombang_id`, `akun_username`, `akun_password_plain` |
| `GelombangPpdb` | `gelombang_ppdbs` | `nama`, `tahun_ajaran`, `tanggal_buka`, `tanggal_tutup`, `kuota`, `is_aktif` |
| `PersyaratanPpdb` | `persyaratan_ppdbs` | `judul`, `deskripsi`, `urutan`, `is_aktif` |
| `FaqPpdb` | `faq_ppdbs` | `pertanyaan`, `jawaban`, `urutan`, `is_aktif` |

### Controllers
- `PpdbController` — Halaman landing PPDB, form daftar, cetak bukti, cek status pendaftar
- `PendaftarController` — Dashboard pendaftar (role=3) untuk kelola & upload berkas pendaftaran
- `Admin\PpdbController` — Review pendaftar, cetak laporan, export data, verifikasi, terima (auto-upgrade ke Siswa), tolak
- `Admin\GelombangPpdbController` — CRUD Gelombang PPDB
- `Admin\PersyaratanPpdbController` — CRUD Persyaratan PPDB
- `Admin\FaqPpdbController` — CRUD FAQ PPDB

### Routes
```
GET  /ppdb                                  ppdb.landing
GET  /ppdb/daftar                           ppdb.index
POST /ppdb/daftar                           ppdb.store
GET  /pendaftar/dashboard                   pendaftar.dashboard
POST /pendaftar/dokumen/{field}             pendaftar.dokumen.upload
POST /admin/ppdb/{pendaftar}/verifikasi     admin.ppdb.verifikasi
POST /admin/ppdb/{pendaftar}/terima         admin.ppdb.terima
```

---

## ✅ Fase 10 — Laporan & Integrasi

**Selesai.**

### Yang diimplementasi
- **Export Data** — Export nilai, absensi, tagihan SPP, dan data pendaftar ke Excel menggunakan library `Maatwebsite\Excel`
- **Export PDF** — Cetak raport resmi siswa ke file PDF menggunakan `Barryvdh\DomPDF`
- **Dashboard Analytics** — Grafik interaktif rekap pembayaran, tren absensi bulanan, dan sebaran prestasi

---

## ✅ Fase 11 — RBAC Guru (Hak Akses Menu)

**Selesai.**

### Yang diimplementasi
- **Granular Permission** — Pembatasan hak akses menu bagi guru berdasarkan konfigurasi per individu oleh admin
- **Daftar Menu Dikontrol**:
  - `menu_jadwal` → Jadwal Mengajar (`guru.jadwal`)
  - `menu_mapel` / `menu_materi` → Mata Pelajaran (`guru.mapel` / `guru.materi.*`)
  - `menu_absensi` → Input Absensi Kelas (`guru.absensi.*`)
  - `menu_nilai` / `menu_tugas` → Input Nilai / Tugas (`guru.nilai.*` / `guru.tugas.*`)
  - `menu_pkl` → PKL Bimbingan (`guru.pkl.*`)
  - `menu_ekskul` → Ekskul Binaan (`guru.ekskul.*`)

### Models & Tabel
- `GuruPermission` → `guru_permissions` — kolom: `guru_id` (UNIQUE), 6 status boolean (`menu_jadwal`, `menu_mapel`, dll.)

### Middleware & Sidebar
- Middleware `CheckGuruPermission` (alias: `guru.can:menu_key`) mereject akses jika flag permission bernilai `false` (abort 403)
- Kondisi `@if(auth()->user()->guru?->canAccess('menu_absensi'))` di sidebar blade untuk menyembunyikan link secara dinamis

### Routes
```
GET  /admin/guru/{guru}/permission          admin.guru.permission.edit
PUT  /admin/guru/{guru}/permission          admin.guru.permission.update
```

---

## ✅ Fase 12 — Struktur Organisasi Sekolah

**Selesai.**

### Yang diimplementasi
- **Jabatan & Anggota** — Pengaturan susunan jabatan hierarkis (Kepala Sekolah, Waka, Kaprodi, dst.) dan anggota pengisi jabatan (bisa Guru internal maupun Staf/tamu eksternal)
- **Tampilan Hierarki Bagan** — Struktur visual organisasi di halaman publik (`/organisasi`) menggunakan CSS flexbox tree responsive tanpa library tambahan

### Models & Tabel
- `JabatanOrganisasi` → `jabatan_organisasis` — kolom: `nama_jabatan`, `parent_id` (self-referencing), `urutan`, `tipe_hubungan` (utama/koordinasi)
- `AnggotaOrganisasi` → `anggota_organisasis` — kolom: `jabatan_organisasi_id`, `nama`, `guru_id` (nullable), `foto` (nullable), `nip`, `periode`, `is_aktif`

### Routes
```
GET  /admin/organisasi                      admin.organisasi.index
GET  /admin/organisasi/{organisasi}/anggota  admin.organisasi.anggota
GET  /organisasi                            landing.organisasi  (Publik)
```

---

## ✅ Fase 13 — Import Excel Master Data

**Selesai.**

### Yang diimplementasi
- **Batch Import** — Import data Guru, Siswa, Mapel, dan Tagihan SPP secara massal via file `.xlsx` / `.xls`
- **Skema Default Account**:
  - Guru: password default NIP, username NIP
  - Siswa: password default NIS, username NIS
- **Import Validation** — Pengecekan data wajib, validasi relasi kelas/jurusan, pengembalian log hasil import (Summary sukses & gagal per baris)

### Imports Classes (`app/Imports/`)
- `GuruImport`, `SiswaImport`, `MapelImport`, `TagihanSppImport` (Implements `ToCollection`, `WithHeadingRow`, `WithValidation`)

### Routes
```
GET  /admin/import                          admin.import.index
GET  /admin/import/template/{type}          admin.import.template  (Download .xlsx)
POST /admin/import                          admin.import.store
```

---

## ✅ Fase 14 — E-Learning (Materi & Tugas)

**Selesai.**

### Yang diimplementasi
- **Manajemen Materi** — Guru mempublikasikan materi e-learning bertipe File, Tautan (Link), atau Teks kaya. Siswa dapat melihat dan mendownload materi tersebut sesuai jadwal kelasnya
- **Pengumpulan Tugas** — Guru membuat tugas dengan tenggat waktu (deadline). Siswa mengumpulkan tugas dalam bentuk upload file, tautan, atau teks essay
- **Penilaian Tugas** — Guru mereview pengumpulan tugas, memberikan nilai angka (maksimal dinamis), serta feedback tertulis secara langsung

### Models & Tabel
| Model | Tabel | Kolom Utama |
|-------|-------|-------------|
| `Materi` | `materis` | `jadwal_id`, `guru_id`, `judul`, `tipe` (file, link, teks), `konten` (file path / link / teks), `is_published` |
| `Tugas` | `tugas` | `jadwal_id`, `guru_id`, `judul`, `instruksi`, `deadline`, `nilai_maksimal`, `tipe_pengumpulan` |
| `PengumpulanTugas` | `pengumpulan_tugas` | `tugas_id`, `siswa_id`, `konten`, `nilai`, `feedback`, `dinilai_at`, `dinilai_oleh` |

### Controllers
- `Guru\MateriController` — Kelola materi per jadwal mengajar
- `Guru\TugasController` — Kelola tugas, lihat pengumpul, beri nilai & feedback
- `Siswa\MateriController` — Lihat materi per mata pelajaran
- `Siswa\TugasController` — Lihat daftar tugas, sisa waktu/deadline, form upload pengumpulan

### Routes
```
GET  /guru/materi/{jadwal}/create           guru.materi.create
POST /guru/tugas/nilai/{pengumpulan}        guru.tugas.nilai
GET  /siswa/materi/detail/{materi}          siswa.materi.detail
POST /siswa/tugas/{tugas}/kumpul            siswa.tugas.kumpul
```

---

## ✅ Fase 15 — Website CMS & Landing Page

**Selesai.**

### Yang diimplementasi
- **Landing Page Dinamis** — Halaman utama sekolah, tentang sekolah, program keahlian, dan struktur organisasi terintegrasi database
- **Manajemen Artikel / Berita** — Admin memposting berita sekolah lengkap dengan kategori, cover image, slug, dan rich text editor
- **Galeri Foto** — Pengelolaan album galeri dan foto kegiatan sekolah dinamis
- **Halaman Statis** — Pembuatan halaman custom (contoh: Visi Misi, Sejarah) yang dapat langsung diakses publik via slug
- **Pengaturan Umum** — Konfigurasi metadata SEO, nama sekolah, email, no HP, alamat, koordinat peta, logo, dan profil sosial media

### Models & Tabel
- `Artikel` → `artikels` (judul, slug, konten, cover, is_published, views)
- `Galeri` → `galeris` (nama, deskripsi, cover, tanggal, is_published)
- `GaleriItem` → `galeri_items` (galeri_id, foto, keterangan, urutan)
- `HalamanStatis` → `halaman_statis` (slug, judul, konten, meta_desc, is_published)
- `Pengaturan` → `pengaturans` (key, value, grup, label, tipe)

---

## ✅ Fase 16 — Integrasi WhatsApp & Kontak

**Selesai.**

### Yang diimplementasi
- **Notifikasi WhatsApp** — Pengiriman pesan otomatis (template berbasis event) menggunakan WhatsApp API Gateway eksternal
- **WhatsApp Logs** — Pencatatan log status kirim pesan WhatsApp (status: sent, failed, response log) untuk memantau traffic notifikasi
- **Hubungi Kami** — Form kontak pada website publik yang mengirimkan pesan langsung ke dashboard admin
- **Sitemap Generator** — Auto-update file `sitemap.xml` untuk optimasi indeks search engine (SEO)

### Models & Tabel
- `WaLog` → `wa_logs` (phone, message, event, status, response)
- `PesanKontak` → `pesan_kontaks` (nama, email, no_hp, subjek, pesan, is_read, read_at)

### Services & Controllers
- `App\Services\WhatsAppService` — Driver pengirim payload API WhatsApp Gateway
- `App\Services\WaTemplates` — Penyusun template pesan WhatsApp dinamis (misal: Tagihan SPP baru, Notifikasi Absensi alpha)
- `Admin\WaLogController` — Dashboard monitor logs pesan WA oleh admin
- `KontakController` / `Admin\KontakController` — Handler pengiriman dari public form dan review pesan masuk oleh admin

---

## ✅ Fase 17 — API Mobile

**Selesai.**

### Yang diimplementasi
- **Sanctum Authentication** — Secure token-based API Auth (`/api/v1/login`, `/api/v1/me`, `/api/v1/logout`)
- **JSON Force Middleware** — Memaksa semua request dan response API dalam format standard `application/json`
- **Siswa API Mobile**:
  - Profile siswa, jadwal pelajaran, absensi kelas, raport hasil belajar
  - Absensi & jurnal PKL (Geo-GPS + image attachment)
  - Info & history tagihan SPP
  - Pengumuman & Notifikasi (in-app mark-as-read)
- **Guru API Mobile**:
  - Jadwal mengajar, rekap & input absensi siswa, input nilai mata pelajaran, overview bimbingan PKL

### Routes (`routes/api.php`)
- Terproteksi middleware `auth:sanctum` dengan namespace `App\Http\Controllers\Api\`

---

## Struktur Direktori Saat Ini

```
app/
  Console/
  Http/
    Controllers/
      Admin/
        AbsensiController.php
        ArtikelController.php
        EkskulController.php
        FaqPpdbController.php
        GaleriController.php
        GelombangPpdbController.php
        GuruController.php
        GuruPermissionController.php
        HalamanStatisController.php
        ImportController.php
        JadwalController.php
        JurusanController.php
        KelasController.php
        KontakController.php
        LaporanKeuanganController.php
        MapelController.php
        NilaiController.php
        PelanggaranController.php
        PembayaranSppController.php
        PenempatanPklController.php
        PengaturanController.php
        PengumumanController.php
        PersyaratanPpdbController.php
        PklController.php
        PpdbController.php
        PrestasiController.php
        SiswaAdminController.php
        StrukturOrganisasiController.php
        SuratController.php
        TagihanSppController.php
        TahunAjaranController.php
        TarifSppController.php
        TempatPklController.php
        WaLogController.php
      Api/
        AuthController.php
        Guru/
          AbsensiController.php
          JadwalController.php
          NilaiController.php
          PklController.php
        Siswa/
          AbsensiController.php
          JadwalController.php
          KesiswaanController.php
          NilaiController.php
          NotifikasiController.php
          PengumumanController.php
          PklController.php
          RaportController.php
          SppController.php
      Guru/
        AbsensiController.php
        EkskulController.php
        MateriController.php
        NilaiController.php
        PklController.php
        TugasController.php
      Siswa/
        AbsensiController.php
        KesiswaanController.php
        MateriController.php
        NilaiController.php
        PklController.php
        SppController.php
        TugasController.php
      AdminController.php
      BeritaController.php
      GaleriPublikController.php
      GuruDashboardController.php
      LandingController.php
      MidtransWebhookController.php
      NotifikasiController.php
      PendaftarController.php
      PengumumanController.php
      PpdbController.php
      ProfileController.php
      SiswaController.php
      SitemapController.php
    Middleware/
      CheckGuruPermission.php
      ForceJsonResponse.php
      RoleMiddleware.php
  Imports/
    GuruImport.php
    MapelImport.php
    SiswaImport.php
    TagihanSppImport.php
  Models/
    Absensi.php
    AbsensiEkskul.php
    AbsensiPkl.php
    AnggotaEkskul.php
    AnggotaOrganisasi.php
    Artikel.php
    Ekskul.php
    FaqPpdb.php
    Galeri.php
    GaleriItem.php
    GelombangPpdb.php
    Guru.php
    GuruPermission.php
    HalamanStatis.php
    JabatanOrganisasi.php
    Jadwal.php
    JurnalPkl.php
    Jurusan.php
    Kelas.php
    Mapel.php
    Materi.php
    Nilai.php
    NilaiPkl.php
    Notifikasi.php
    Pelanggaran.php
    PembayaranSpp.php
    PendaftarPpdb.php
    PenempatanPkl.php
    Pengaturan.php
    PengumpulanTugas.php
    Pengumuman.php
    PersyaratanPpdb.php
    PesanKontak.php
    Prestasi.php
    Semester.php
    Siswa.php
    SuratKeterangan.php
    TagihanSpp.php
    TahunAjaran.php
    TarifSpp.php
    TempatPkl.php
    Tugas.php
    User.php
    WaLog.php
  Services/
    MidtransService.php
    WaTemplates.php
    WhatsAppService.php
resources/
  views/
    admin/
      absensi/
      cms/
      guru/
      import/
      jadwal/
      jurusan/
      kelas/
      kesiswaan/
      keuangan/
      kontak/
      mapel/
      nilai/
      organisasi/
      pengumuman/
      pkl/
      ppdb/
      siswa/
      tahun-ajaran/
      whatsapp/
    guru/
      absensi/
      ekskul/
      materi/
      nilai/
      pkl/
      tugas/
    siswa/
      kesiswaan/
      materi/
      pkl/
      spp/
      tugas/
      absensi.blade.php
      dashboard.blade.php
      jadwal.blade.php
      mapel.blade.php
      nilai.blade.php
      raport.blade.php
    public/
      berita/
      galeri/
      landing/
      partials/
    ppdb/
    pendaftar/
    layouts/
      admin.blade.php
      app.blade.php
      public.blade.php
      partials/
        sidebar.blade.php
        navbar.blade.php
```

---

## Catatan Teknis

### Pola & Integrasi yang Digunakan
- **updateOrCreate** — untuk upsert nilai, absensi, dan data keuangan agar idempoten.
- **Midtrans API** — Transaksi pembayaran digital otomatis terkonfirmasi via webhook API (Snap Token).
- **Sanctum Auth** — Otentikasi berbasis token untuk aplikasi mobile client.
- **WhatsApp API Integration** — Event-driven template notifications menggunakan HTTP Client.
- **Granular RBAC** — Custom middleware `CheckGuruPermission` untuk melayani izin menu per guru secara dinamis.
- **Hierarchical Tree CSS** — Visualisasi bagan organisasi memanfaatkan flexbox tree responsive tanpa overhead JavaScript.
