# Fitur SIAKAD

Daftar lengkap fitur SIAKAD, dikelompokkan per bagian dan peran pengguna. Untuk instalasi, lihat [README](README.md).

## Website publik

- **Halaman sekolah**: beranda, profil, program keahlian, struktur organisasi, berita, galeri, prestasi, kontak, dan sitemap. Semua isi dikelola dari CMS.
- **Portal PPDB**
  - Jadwal gelombang pendaftaran, hitung mundur penutupan, dan sisa kuota.
  - Formulir pendaftaran online.
  - Cek status dengan nomor pendaftaran atau NISN + tanggal lahir.
  - Cetak bukti pendaftaran.
  - Persyaratan dokumen dan FAQ.

## Admin

- **Data master**: tahun ajaran, semester, jurusan, kelas, mapel, guru (beserta hak akses per guru), siswa, dan orang tua. Mendukung impor dari Excel.
- **Akademik**: jadwal pelajaran, absensi, nilai, dan rapor PDF.
- **PPDB**: gelombang, persyaratan, FAQ, verifikasi berkas, terima/tolak pendaftar, dan ekspor data.
- **Keuangan**: tarif dan tagihan SPP, pembayaran tunai maupun online (Midtrans), konfirmasi pembayaran, kwitansi, laporan keuangan, dan daftar tunggakan.
- **Kesiswaan**: ekstrakurikuler, pelanggaran, prestasi, dan surat keterangan (aktif, pindah, lulus).
- **PKL**: tempat PKL dan penempatan siswa.
- **CMS**: artikel, galeri, halaman statis, struktur organisasi, dan pengaturan sekolah (nama, logo, alamat, kontak).
- **Sistem**: pengumuman, log notifikasi WhatsApp, audit log, mode maintenance, dan backup otomatis.

## Guru

- Absensi siswa.
- Materi dan tugas, termasuk menilai pengumpulan tugas.
- Input nilai.
- Mengelola ekstrakurikuler binaan.
- Pembimbing PKL.
- Ujian online.

## Siswa

- Jadwal, materi, tugas, nilai, dan absensi.
- Tagihan SPP, bisa dibayar online.
- Jurnal dan absensi PKL.
- Catatan kesiswaan.
- Ujian online.

## Orang tua

- Memantau absensi, nilai, catatan kesiswaan, dan tagihan SPP anak.

## Pendaftar PPDB

- Mengunggah berkas persyaratan.
- Memantau status pendaftaran.

## Ujian Online (CBT)

Modul `app/Modules/Ujian`, diakses di `/ujian` dengan akun SIAKAD yang sama.

- **Admin**: periode dan fase ujian (termasuk paksa buka/tutup), melihat semua ujian, dan menugaskan guru pengawas.
- **Guru**
  - Bank soal per mapel.
  - Soal pilihan ganda dan essay, dengan format teks dan gambar.
  - Soal dan opsi jawaban diacak per siswa.
  - Koreksi essay.
  - Ekspor hasil ke Excel/PDF.
- **Pengawas**
  - Memantau peserta secara langsung: status online, progres, sisa waktu, dan pelanggaran.
  - Menambah waktu dan mengirim pengumuman ke peserta.
  - Paksa selesai, diskualifikasi, dan reset sesi.
- **Siswa**
  - Masuk dengan NIS di `/masuk-ujian`.
  - Timer mengikuti waktu server.
  - Jawaban tersimpan otomatis dan tetap aman saat koneksi putus.
  - Deteksi pindah tab dan copy-paste.
  - Satu perangkat per sesi.

## Lain-lain

- **Akun dan keamanan**: verifikasi dua langkah (2FA), lupa password, dan reset password.
- **Notifikasi**: di dalam aplikasi dan lewat WhatsApp, termasuk pengingat SPP otomatis setiap pukul 08.00.
- **API mobile**: endpoint untuk siswa dan guru (Laravel Sanctum).
- **Installer web** untuk pemasangan pertama.
