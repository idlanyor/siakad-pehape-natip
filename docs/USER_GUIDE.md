# Panduan Pengguna — SIAKAD SMK

Sistem Informasi Akademik untuk SMK. Dokumen ini menjelaskan fitur per peran.

## Peran & Akses

| Role | Kode | Akses Utama |
|------|------|-------------|
| Admin | 0 | Seluruh manajemen sekolah |
| Guru | 1 | Mengajar, absensi, nilai, materi/tugas, PKL, ekskul |
| Siswa | 2 | Lihat jadwal, nilai, absensi, SPP, e-learning |
| Pendaftar | 3 | Portal PPDB (upload berkas) |
| Orang Tua | 4 | Pantau anak: nilai, absensi, kesiswaan, bayar SPP |

Login di `/login` menggunakan **username** + password. Jika 2FA aktif, masukkan
kode OTP dari aplikasi authenticator setelah password benar.

---

## Admin

**Master Data:** Jurusan, Kelas, Mapel, Guru, Siswa, Jadwal, Tahun Ajaran/Semester.
Mendukung **import Excel** massal (Guru/Siswa/Mapel/Tagihan).

**Orang Tua / Wali:** Buat akun orang tua, hubungkan ke satu/lebih siswa
(menu: PENGGUNA → Orang Tua / Wali).

**Akademik:** Pantau absensi & nilai per jadwal, cetak raport (PDF).

**Keuangan/SPP:** Atur tarif per kelas, generate tagihan bulanan, konfirmasi
pembayaran manual, integrasi Midtrans (bayar online). Cetak kwitansi.

**PPDB:** Kelola gelombang, verifikasi pendaftar, terima (auto-buat akun siswa).

**CMS Website:** Artikel/berita, galeri, halaman statis, pengaturan umum.

**Laporan (menu Laporan):** Rekap Kehadiran, Nilai, Keuangan, PPDB — tampil di
layar & export **PDF**.

**Audit Log:** Riwayat semua perubahan data penting (siapa, kapan, apa).
Filter & export Excel.

**Keamanan:** Aktifkan 2FA via ikon perisai di navbar.

---

## Guru

- **Jadwal Mengajar** — lihat jadwal sesuai penugasan.
- **Absensi** — input kehadiran siswa per jadwal per tanggal.
- **Nilai** — input 6 komponen nilai; sistem hitung rata-rata & predikat otomatis.
- **E-Learning** — unggah materi (file/link/teks), buat tugas berdeadline, nilai
  pengumpulan siswa.
- **PKL** — bimbing siswa, beri nilai PKL.
- **Ekskul** — absensi & rekap kehadiran ekskul binaan.

> Menu guru dapat dibatasi admin per individu (RBAC).

---

## Siswa

- Jadwal pelajaran, nilai per mapel, raport mandiri.
- Rekap absensi pribadi.
- **SPP** — lihat tagihan, upload bukti bayar, atau bayar online (Midtrans).
- **E-Learning** — unduh materi, kumpulkan tugas (file/teks/link).
- **PKL** — jurnal harian, absensi GPS + foto.
- Kesiswaan: pelanggaran, prestasi, ekskul.
- Pengumuman & notifikasi.

---

## Orang Tua / Wali

Login dengan akun yang dibuat admin. Jika punya >1 anak, gunakan **dropdown
pemilih anak** di atas halaman.

- **Dashboard** — ringkasan kehadiran, poin pelanggaran, prestasi, tagihan.
- **Nilai Anak** — rekap nilai per mapel.
- **Absensi Anak** — persentase kehadiran per mapel.
- **Kesiswaan Anak** — pelanggaran & prestasi.
- **Pembayaran SPP** — lihat tagihan anak & bayar online (Midtrans).

---

## Pendaftar (PPDB)

1. Daftar di halaman publik `/ppdb/daftar` (tanpa login).
2. Dapat akun otomatis → login untuk upload berkas (foto, ijazah, KK, dll).
3. Pantau status seleksi. Jika diterima, akun otomatis berubah jadi Siswa.

---

## Notifikasi WhatsApp

Sistem mengirim WA otomatis (jika diaktifkan) untuk: tagihan SPP baru,
pembayaran berhasil, siswa alpa, pengumuman, dan penerimaan PPDB.
Riwayat kirim ada di menu admin WhatsApp Log.
