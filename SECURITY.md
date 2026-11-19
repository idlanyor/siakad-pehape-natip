# Kebijakan Keamanan

SIAKAD menyimpan data pribadi siswa, orang tua, dan guru. Kami sangat menghargai laporan celah keamanan.

## Melaporkan celah

**Jangan** membuka issue publik untuk celah keamanan.

Laporkan secara privat lewat **GitHub → tab Security → Report a vulnerability** di repositori ini. Sertakan:

- deskripsi celah dan dampaknya,
- langkah untuk mereproduksi (atau proof of concept),
- versi/commit yang terdampak.

Kami akan merespons secepatnya dan mengabari perkembangan perbaikannya. Setelah perbaikan dirilis, kami akan mencantumkan nama pelapor bila berkenan.

## Untuk sekolah yang memasang SIAKAD

- Selalu gunakan versi terbaru dari branch `main`.
- Jangan menjalankan `DummyDataSeeder`/`LengkapSeeder` di server produksi; seeder tersebut membuat akun dengan password `password`.
- Set `APP_ENV=production` dan `APP_DEBUG=false`.
- Ganti password admin dan aktifkan verifikasi dua langkah (2FA) di menu profil.
