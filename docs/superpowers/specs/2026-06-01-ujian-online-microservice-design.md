# Desain: Modul Ujian Online sebagai Microservice

**Tanggal:** 2026-06-01
**Status:** Disetujui (siap masuk tahap perencanaan implementasi)
**Konteks:** SIAKAD (Laravel 11 monolith) — memisahkan modul Ujian Online jadi service terpisah.

---

## 1. Ringkasan & Keputusan Arsitektur

Modul Ujian Online dipisah dari SIAKAD menjadi **service Laravel terpisah** dengan database, UI, dan deployment sendiri. Tujuannya: isolasi beban saat ujian serentak, deploy independen, reusable lintas produk, dan pemisahan kodebase.

| Aspek | Keputusan |
|---|---|
| Bentuk | App Laravel ke-2 terpisah, DB sendiri, Blade UI sendiri, subdomain (`ujian.sekolah.id`) |
| Akses ref data (siswa/kelas/mapel/guru/jadwal) | Call SIAKAD REST API on-demand (read-only), disimpan sebagai snapshot ringan di `ujian_db` |
| Auth siswa/guru | Token handoff: SIAKAD menerbitkan signed token (JWT) → service ujian verifikasi signature lokal → buat session sendiri |
| Auth antar service | API key (`X-Service-Key`) terpisah dari token user |
| Realtime | Live monitoring proktor via websocket (Laravel Reverb), dengan polling fallback |
| Proktoring | Live status siswa + event log (tab switch / blur / copy / paste) |
| Periode ujian | Sistem berfase (input soal → try out → pelaksanaan → remedial → rekapitulasi), transisi otomatis by tanggal + bisa override manual admin |

### Tension yang disadari & diterima

- **Shared DB ditolak** karena mematahkan tujuan "reusable lintas produk" dan melemahkan "deploy independen". Diganti: **DB terpisah + API ref**.
- Konsekuensi: ada coupling runtime ke SIAKAD saat penerbitan token & sinkronisasi data ref. Dimitigasi dengan: verifikasi token lokal (tak perlu call balik tiap request) + snapshot data ref, sehingga **saat ujian berlangsung, service ujian tetap jalan walau SIAKAD mati**.

---

## 2. Topologi & Tanggung Jawab

```
┌─────────────────┐         ┌──────────────────────┐
│  SIAKAD (mono)  │         │  UJIAN SERVICE        │
│  siakad.id      │         │  ujian.sekolah.id     │
│                 │         │                       │
│ • Master data   │◄───────►│ • Periode & fase      │
│   siswa/kelas/  │  REST   │ • Bank soal           │
│   mapel/jadwal  │  (API   │ • Susun ujian & soal  │
│ • Issue token   │  ref +  │ • Sesi & jawaban      │
│ • Tampil nilai  │  token  │ • Penilaian auto/man  │
│   akhir (sync)  │  verify)│ • Proktor live        │
│ DB: siakad_db   │         │ • Hasil & analisis    │
└─────────────────┘         │ • RBAC tim admin      │
                            │ • Blade UI ujian      │
                            │ DB: ujian_db          │
                            └──────────────────────┘
                                      │ websocket (Reverb)
                                      ▼
                              guru proktor / siswa
```

**SIAKAD memiliki:** master data, identitas user, penerbit token, penerima nilai akhir.
**Service ujian memiliki:** periode/fase, bank soal, soal/sesi/jawaban/nilai, proktor, analisis, RBAC admin, dan UI mengerjakan ujian.

**Aturan boundary:** service ujian **tidak pernah** menulis ke `siakad_db`. Data siswa/kelas masuk via API → disimpan sebagai *snapshot ringan* (id + nama + NIS, secukupnya untuk tampil). Nilai akhir dikirim balik ke SIAKAD via API callback.

---

## 3. Kontrak API antar Service

### SIAKAD expose (dikonsumsi service ujian)

| Endpoint | Guna |
|---|---|
| `POST /api/v1/exam-token/issue` | Terbitkan signed token saat siswa/guru membuka ujian. Body: `user_id`, `role`, `ujian_id`, `exp`. |
| `GET /api/v1/exam-token/verify` | (Opsional) verifikasi token + balas identitas + ref. Umumnya verifikasi dilakukan lokal oleh service ujian. |
| `GET /api/v1/ref/kelas/{id}/siswa` | Daftar siswa satu kelas (untuk guru assign peserta). |
| `GET /api/v1/ref/guru/{id}`, `/ref/mapel/{id}`, `/ref/jadwal/{id}` | Snapshot ref data. |

### Service ujian expose (dikonsumsi SIAKAD)

| Endpoint | Guna |
|---|---|
| `GET /api/v1/ujian?kelas_id=&status=` | SIAKAD tampilkan daftar/badge ujian di dashboard siswa/guru. |
| `GET /api/v1/hasil/{ujian_id}` | Nilai akhir per siswa (untuk SIAKAD tarik ke rapor/nilai). |
| `POST /api/v1/hasil/callback` (atau SIAKAD pull) | Pengiriman nilai akhir ketika sesi selesai dikoreksi. |

### Auth antar service

- Service-to-service: header `X-Service-Key` (rahasia bersama), terpisah dari token user.

### Token handoff (signed)

- JWT HS256, secret bersama antara SIAKAD & service ujian.
- Claim: `sub` (user_id), `role`, `ujian_id`, `periode_fase`, `siswa_snapshot` (id/nama/nis/kelas), `exp` (±2 menit).
- Service ujian verifikasi signature **lokal** → buat session ujian sendiri. Tidak perlu call balik ke SIAKAD per request.

---

## 4. Skema Database `ujian_db`

Semua tabel ujian berada di DB sendiri. Ref data SIAKAD = kolom snapshot (denormalized), **bukan** foreign key lintas-DB.

### 4.1 Periode & Fase

```
periode_ujian
  id, nama ("UAS Ganjil 2026/2027"),
  tahun_ajaran, semester,
  status(draft|aktif|selesai),
  fase_aktif (cache: hasil hitung tanggal + override),
  timestamps

fase_periode
  id, periode_id(FK),
  tipe(input_soal|try_out|pelaksanaan|remedial|rekapitulasi),
  urutan, tgl_mulai, tgl_selesai,
  override_status(null|paksa_buka|paksa_tutup),
  override_oleh, override_at,
  timestamps
```

### 4.2 Ujian & Soal

```
ujian
  id, periode_id(FK), judul, deskripsi, tipe(quiz|uts|uas|remidi),
  -- ref snapshot (dari SIAKAD, bukan FK):
  jadwal_id, mapel_id, mapel_nama, kelas_id, kelas_nama, guru_id, guru_nama,
  -- milik ujian:
  waktu_mulai, waktu_selesai, durasi_menit, passing_grade,
  acak_soal(bool), acak_opsi(bool), status(draft|published|selesai),
  is_published, jumlah_peserta, timestamps

soal_ujian
  id, ujian_id(FK), bank_soal_id(FK nullable), nomor,
  tipe(pilihan_ganda|essay),
  pertanyaan (rich text/HTML), opsi_a..e, kunci_jawaban, bobot_nilai,
  timestamps
```

### 4.3 Sesi & Jawaban

```
sesi_ujian
  id, ujian_id(FK),
  -- ref snapshot siswa:
  siswa_id, siswa_nama, siswa_nis, kelas_id,
  status(belum|berlangsung|selesai|timeout|diskualifikasi),
  is_resmi(bool), fase(try_out|pelaksanaan|remedial),
  waktu_mulai, waktu_selesai, durasi_detik,
  nilai_otomatis, nilai_manual, nilai_akhir, predikat,
  tab_switch_count, timestamps

jawaban_siswa
  id, sesi_ujian_id(FK), soal_ujian_id(FK),
  jawaban_pilihan, jawaban_text, nilai, is_benar, timestamps

proktor_event   -- log + sumber payload realtime
  id, sesi_ujian_id(FK),
  tipe(tab_switch|blur|copy|paste|fullscreen_exit|reconnect),
  detail(json), created_at
```

### 4.4 Bank Soal (sekunder)

```
bank_soal
  id, mapel_id, mapel_nama, topik, tingkat_kesulitan(mudah|sedang|sulit),
  tag(json), tipe(pilihan_ganda|essay),
  pertanyaan (rich text), opsi_a..e, kunci_jawaban, bobot_default,
  dibuat_oleh, timestamps
```

`soal_ujian.bank_soal_id` mereferensikan asal soal. Saat menyusun ujian, soal **disalin** dari bank ke `soal_ujian` (snapshot), agar perubahan bank tidak mengubah ujian yang sudah berjalan.

### 4.5 Konten campuran (sekunder)

```
media_soal   -- polymorphic
  id, owner_type(soal_ujian|bank_soal|opsi), owner_id,
  jenis(gambar|audio), path, mime, ukuran, timestamps
```

Storage media di service ujian (local/S3), bukan di SIAKAD.

### 4.6 RBAC tim admin (sekunder)

```
admin_role
  id, nama, is_preset(bool), timestamps

admin_permission
  id, key (mis. bank_soal.kelola, proktor.monitor, nilai.koreksi, periode.kelola)

admin_role_permission  -- pivot
  admin_role_id, admin_permission_id

admin_user_role  -- pivot user ujian ↔ role
  user_id, admin_role_id
```

Preset role: **Super Admin**, **Admin Soal**, **Admin Pengawas**, **Admin Nilai**. Admin bisa membuat role custom dengan kombinasi permission. Independen dari role integer SIAKAD.

### Index penting

- `sesi_ujian(ujian_id, siswa_id)` UNIQUE
- `jawaban_siswa(sesi_ujian_id, soal_ujian_id)` UNIQUE
- `ujian(periode_id, kelas_id, status)`
- `proktor_event(sesi_ujian_id, created_at)`

### Catatan snapshot

Ref snapshot diisi sekali saat ujian/sesi dibuat. Jika nama siswa berubah di SIAKAD, tidak auto-sync — ini disengaja (snapshot = potret saat ujian).

---

## 5. Mesin Periode/Fase

### Aturan transisi

- **Fase aktif dihitung server** dari `now()` (jam server) vs rentang `tgl_mulai`/`tgl_selesai`.
- Jika `override_status` di-set, override menang atas perhitungan tanggal.
- Scheduled job (per menit) me-refresh `periode_ujian.fase_aktif` + broadcast perubahan. Namun **sumber kebenaran tetap dihitung on-read** (server-authoritative); job hanya untuk notifikasi/cache.
- **Override:** admin/proktor klik "Paksa buka fase X" / "Paksa tutup" → set `override_status` + `override_oleh`/`override_at`. Bisa direset kembali ke otomatis.

### Gating per fase (di-enforce di middleware/policy, bukan hanya UI)

| Fase aktif | Guru | Siswa |
|---|---|---|
| `input_soal` | CRUD soal ✓ | — |
| `try_out` | soal terkunci | kerjakan mode latihan (`is_resmi=false`, nilai tak masuk hasil resmi) |
| `pelaksanaan` | monitor/proktor | kerjakan resmi (`is_resmi=true`, `nilai_akhir` dihitung) |
| `remedial` | input nilai manual | siswa belum lulus kerjakan ulang |
| `rekapitulasi` | review/export | lihat hasil (read-only) |
| `selesai` | terkunci | terkunci |

Contoh enforcement: siswa mencoba submit ujian resmi saat fase `try_out` → ditolak server.

---

## 6. Alur Utama

### A. Guru menyusun ujian
```
guru @ujian.id → form → pilih kelas (call SIAKAD /ref/kelas/{id}/siswa)
  → snapshot peserta tersimpan → input soal (atau tarik dari bank) → publish
  → notifikasi siswa (call SIAKAD API)
```

### B. Siswa mulai (token handoff)
```
siswa @SIAKAD → klik "Kerjakan" → SIAKAD POST /exam-token/issue
  → redirect ke ujian.id/masuk?token=xxx
  → service ujian verify signature lokal → buat session ujian
  → cek fase_aktif periode → tentukan mode (try_out|pelaksanaan|remedial)
  → buat sesi_ujian(status=berlangsung, is_resmi, fase)
  → halaman kerjakan (timer server-authoritative)
```

### C. Mengerjakan + autosave
```
tiap jawab → POST /sesi/{id}/jawab (upsert jawaban_siswa)   ← idempoten
window blur / tab switch / copy → POST /proktor/event → simpan + broadcast Reverb
```

### D. Submit / timeout
```
submit | waktu habis (cek server) → status=selesai
  → koreksi otomatis pilihan_ganda (nilai_otomatis)
  → essay menunggu guru → SesiUjianObserver: saat nilai_akhir terisi → notif siswa
  → jika is_resmi: nilai_akhir → callback SIAKAD /hasil (tarik ke rapor)
```

---

## 7. Realtime (Reverb)

| Channel | Event | Subscriber |
|---|---|---|
| `proktor.ujian.{ujian_id}` (private, guru) | `SesiUpdated`, `ProktorEvent` | dashboard proktor guru |
| `sesi.{sesi_id}` (private, siswa) | `PaksaSelesai`, `Pengumuman` | siswa (guru bisa diskualifikasi/umumkan) |

Polling fallback bila websocket gagal — ujian tetap jalan (degradasi mulus).

---

## 8. Penanganan Error (kritis untuk ujian)

- **Timer = server-authoritative.** Client hanya display; submit divalidasi `waktu_selesai` server. Menutup celah ubah jam lokal.
- **Autosave idempoten.** Upsert by `(sesi, soal)` → refresh/disconnect tidak menghilangkan jawaban.
- **Reconnect.** Siswa kembali → state sesi di-rehydrate dari server, sisa waktu dihitung server. Catat event `reconnect`.
- **SIAKAD down saat issue token?** Pesan jelas + retry. Saat ujian berlangsung SIAKAD tidak dibutuhkan (token verify lokal) → ujian tetap jalan.
- **Callback nilai gagal?** Queue + retry; hasil tetap aman di `ujian_db`.
- **Satu sesi aktif per siswa per ujian** (UNIQUE) → mencegah dobel sesi.

---

## 9. Analisis Butir Soal (sekunder)

Dihitung dari agregat `jawaban_siswa`:

- **Tingkat kesukaran (p-value):** proporsi siswa menjawab benar per soal.
- **Daya beda (discrimination index):** beda performa kelompok atas vs bawah.
- **Analisis distraktor:** sebaran pilihan jawaban (efektivitas pengecoh).
- **Reliabilitas:** indikator konsistensi instrumen.

Disajikan sebagai view/laporan baru di service ujian.

---

## 10. Daftar Fitur & Klasifikasi

| Fitur | Kelas | Catatan |
|---|---|---|
| Periode & fase ujian | MVP | Otomatis by tanggal + override manual |
| Susun ujian + soal teks | MVP | CRUD, peserta snapshot, publish |
| Alur mengerjakan (timer, autosave) | MVP | Server-authoritative, idempoten |
| Koreksi otomatis PG + manual essay | MVP | |
| Hasil, predikat, export Excel/PDF | MVP | Pakai `HasilUjianExport` existing |
| Callback nilai ke SIAKAD | MVP | |
| Proktor live + anti-curang dasar | MVP | Reverb, event log, acak soal/opsi, paksa-selesai |
| Bank soal digital | Sekunder | Master reusable, salin ke ujian |
| RBAC tim admin | Sekunder | Role/permission + preset |
| Konten campuran (gambar/audio) | Sekunder | `media_soal` polymorphic + storage |
| Analisis butir soal | Sekunder | p-value, daya beda, distraktor, reliabilitas |
| Try-out & remedial polish | Sekunder | Mode latihan analitik, alur remedial penuh |

---

## 11. Roadmap Sprint (Agile)

### MVP

- **Sprint 1 — Pondasi service:** scaffold app Laravel ke-2, `ujian_db`, token handoff SSO, API key service-to-service, layout Blade ujian, migrasi controller/view existing.
- **Sprint 2 — Periode & fase:** `periode_ujian` + `fase_periode`, mesin fase otomatis+override, gating middleware/policy.
- **Sprint 3 — Susun & kerjakan ujian:** CRUD ujian + soal (teks), peserta snapshot, alur kerjakan, autosave idempoten, timer server, submit/timeout.
- **Sprint 4 — Koreksi & hasil:** koreksi otomatis PG, koreksi manual essay, nilai_akhir, predikat, callback SIAKAD, export Excel/PDF.
- **Sprint 5 — Proktor live & anti-curang:** Reverb, dashboard proktor, `proktor_event`, paksa-selesai/diskualifikasi, acak soal/opsi.

### Sekunder

- **Sprint 6 — Bank soal digital**
- **Sprint 7 — RBAC tim admin**
- **Sprint 8 — Konten campuran (gambar/audio)**
- **Sprint 9 — Analisis butir soal**
- **Sprint 10 — Try-out & remedial polish**

Tiap sprint mendapat plan implementasi tersendiri.

---

## 12. Migrasi Kode Existing

Controller & view ujian existing dibangun untuk monolith (pakai layout/sidebar SIAKAD).

**Dipindah ke service ujian** (adaptasi ke layout ujian sendiri):
- `app/Http/Controllers/Admin/UjianController.php`
- `app/Http/Controllers/Admin/HasilUjianController.php`
- `app/Observers/SesiUjianObserver.php`
- `app/Exports/HasilUjianExport.php`
- 8 view blade: `admin/ujian/*`, `guru/ujian/*`, `siswa/ujian/*`, `admin/proktor/*`

**Tetap di SIAKAD** (diadaptasi jadi konsumen API):
- Badge/link ujian di dashboard guru/siswa
- `ELearningNotifier` → jadi penerima callback / pengirim notif
- Item menu sidebar

**Catatan auth:** SIAKAD pakai role integer (0=admin, 1=guru, 2=siswa). Service ujian menerima role dari token handoff, tidak mengakses tabel user SIAKAD. RBAC tim admin (Sprint 7) adalah sistem peran internal service ujian, terpisah dari role integer SIAKAD.

---

## Lampiran: Asumsi

- Jam server SIAKAD & service ujian tersinkron (NTP) — penting untuk timer & fase.
- Secret JWT & `X-Service-Key` dikelola via env, dirotasi berkala.
- Storage media (Sprint 8) belum diputuskan local vs S3 — diputuskan saat sprint tersebut.
