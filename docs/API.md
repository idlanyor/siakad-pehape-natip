# SIAKAD — REST API Documentation

> Base URL: `https://domain.sch.id/api/v1`
>
> Authentication: **Laravel Sanctum** (Bearer Token)

---

## Auth

### POST `/login`

Login dan dapatkan token.

**Body (JSON):**
```json
{
  "login": "admin@sekolah.sch.id",
  "password": "password123"
}
```
- `login` bisa berupa email atau username.

**Response 200:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "Admin",
    "username": "admin",
    "email": "admin@sekolah.sch.id",
    "role": 0,
    "role_label": "Admin"
  }
}
```

### POST `/logout`
🔒 Auth required

Revoke current token.

### GET `/me`
🔒 Auth required

Profil user yang sedang login. Siswa & guru menyertakan data relasi.

### PUT `/me`
🔒 Auth required

Update profil.

**Body:**
```json
{
  "name": "Nama Baru",
  "email": "baru@email.com"
}
```

### PUT `/me/password`
🔒 Auth required

Ganti password.

**Body:**
```json
{
  "current_password": "password_lama",
  "password": "password_baru",
  "password_confirmation": "password_baru"
}
```

---

## Siswa API

> Prefix: `/siswa` — Memerlukan auth + role siswa (2)

### GET `/siswa/jadwal`
Jadwal pelajaran semester aktif.

### GET `/siswa/nilai`
Nilai per mata pelajaran semester aktif.

### GET `/siswa/absensi`
Rekap absensi + detail record semester aktif.

### GET `/siswa/raport`
Data raport lengkap (semua mapel + nilai akhir + predikat).

### GET `/siswa/pkl`
Data PKL: penempatan, jurnal, absensi, nilai.

### POST `/siswa/pkl/jurnal`
Submit jurnal harian PKL.

**Body (form-data):**
| Field | Type | Required |
|-------|------|----------|
| tanggal | date | ✅ |
| kegiatan | string | ✅ |
| keterangan | string | ❌ |
| foto | file (image) | ❌ |

### POST `/siswa/pkl/absensi/masuk`
Check-in PKL.

**Body:**
```json
{
  "tanggal": "2026-05-28",
  "latitude": -6.123,
  "longitude": 106.456,
  "foto": "data:image/jpeg;base64,..."
}
```

### POST `/siswa/pkl/absensi/pulang`
Check-out PKL. Same body format.

### GET `/siswa/kesiswaan`
Pelanggaran, prestasi, dan ekskul siswa.

### GET `/siswa/spp`
Daftar tagihan SPP.

### GET `/siswa/spp/{id}`
Detail tagihan + Midtrans snap token (jika belum lunas).

### GET `/siswa/pengumuman`
Daftar pengumuman untuk siswa (paginated, 15/page).

### GET `/siswa/notifikasi`
Daftar notifikasi (paginated, 20/page).

### POST `/siswa/notifikasi/{id}/read`
Tandai notifikasi sudah dibaca.

---

## Guru API

> Prefix: `/guru` — Memerlukan auth + role guru (1)

### GET `/guru/jadwal`
Jadwal mengajar.

### GET `/guru/absensi`
List jadwal untuk input absensi.

### GET `/guru/absensi/{jadwal_id}?tanggal=YYYY-MM-DD`
Form absensi: daftar siswa + status yang sudah diisi.

### POST `/guru/absensi/{jadwal_id}`
Submit absensi.

**Body:**
```json
{
  "tanggal": "2026-05-28",
  "absensi": [
    { "siswa_id": 1, "status": "Hadir", "keterangan": null },
    { "siswa_id": 2, "status": "Alpa", "keterangan": "Tidak ada kabar" }
  ]
}
```
Status: `Hadir`, `Izin`, `Sakit`, `Alpa`

### GET `/guru/nilai/{jadwal_id}`
Form input nilai: daftar siswa + nilai existing.

### POST `/guru/nilai/{jadwal_id}`
Submit nilai.

**Body:**
```json
{
  "nilai": [
    {
      "siswa_id": 1,
      "tugas": 85,
      "pts": 90,
      "pas": 88,
      "praktik": 92,
      "proyek": null,
      "portofolio": null,
      "catatan": "Bagus"
    }
  ]
}
```

### GET `/guru/pkl`
List penempatan PKL yang dibimbing.

---

## Rate Limiting

| Scope | Limit |
|-------|-------|
| Login (`api-auth`) | 60 req/menit per IP |
| Authenticated (`api`) | 120 req/menit per user |

---

## Error Format

Semua error dikembalikan dalam format JSON:

```json
{
  "message": "Deskripsi error.",
  "errors": {
    "field": ["Pesan validasi."]
  }
}
```

| Status | Keterangan |
|--------|------------|
| 200 | OK |
| 201 | Created |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |

---

*Generated: 2026-05-28*
