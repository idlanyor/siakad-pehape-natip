# Panduan Kontribusi

Terima kasih sudah ingin membantu SIAKAD! Semua bentuk kontribusi diterima: laporan bug, usulan fitur, perbaikan dokumentasi, maupun kode.

## Sebelum mulai

- Cek [issue](../../issues) yang sudah ada agar tidak dobel.
- Untuk perubahan besar (fitur baru, ubah skema database), buka issue dulu untuk didiskusikan.
- **Celah keamanan** jangan dilaporkan lewat issue publik — lihat [SECURITY.md](SECURITY.md).

## Menyiapkan lingkungan

Ikuti bagian *Instalasi → B* di [README](README.md) (SQLite + data demo).

## Alur kerja

1. Fork repositori, buat branch dari `main`: `fix/nama-singkat` atau `feat/nama-singkat`.
2. Buat perubahan sekecil dan sefokus mungkin.
3. Tambahkan/ubah test bila mengubah perilaku. Perbaikan bug sebaiknya disertai test yang mereproduksi bug-nya.
4. Pastikan lulus:
   ```bash
   php artisan test
   vendor/bin/pint --dirty   # format hanya file yang kamu ubah
   npm run build
   ```
5. Buka pull request ke `main` dengan penjelasan *apa* yang diubah dan *mengapa*, plus screenshot untuk perubahan tampilan.

## Konvensi

- Bahasa antarmuka, pesan, dan komentar kode: **Bahasa Indonesia**.
- Format PHP mengikuti [Laravel Pint](https://laravel.com/docs/pint). Sebagian kode lama belum terformat; cukup format file yang kamu ubah (`vendor/bin/pint --dirty`), jangan memformat ulang seluruh proyek dalam PR fitur.
- Pesan commit: `jenis(lingkup): ringkasan` — mis. `fix(ppdb): ...`, `feat(ujian): ...`, `docs: ...`.
- Jangan pernah meng-commit `.env`, data siswa asli, atau kredensial apa pun. Gunakan data fiktif dan domain `example.com` / `sekolah.example` untuk contoh.
- Teks yang tampil ke pengguna dan bergantung pada sekolah (nama, alamat, logo) diambil dari `setting()`, bukan ditulis langsung di kode.

## Lisensi

Dengan mengirim kontribusi, kamu setuju kontribusimu dilisensikan di bawah [lisensi MIT](LICENSE) proyek ini.
