# Operasional: Sinkronisasi & Login Siswa Mandiri

## Provisioning akun siswa
Siswa login mandiri di ujian-service memakai tabel lokal `user_siswa`.
Akun dibuat dari data SIAKAD via:

```bash
php artisan siakad:sync-siswa              # semua siswa aktif
php artisan siakad:sync-siswa --kelas=7    # filter kelas tertentu
```

- **Username** = NIS
- **Password default** = NIS (di-hash otomatis)
- `SISWA_SYNC_INTERVAL` di `.env` mengatur interval sinkronasi otomatis (default 3600s)

## Prasyarat
- `SIAKAD_BASE_URL` & `SIAKAD_SERVICE_KEY` di `.env` (ujian-service) cocok dengan SIAKAD
- Endpoint tersedia di SIAKAD: `POST /api/ujian-service/sync/siswa`

## Login
- **URL:** `{UJIAN_SERVICE_URL}/siswa/login`
- **Independen dari SIAKAD** saat runtime — validasi password di tabel `user_siswa` lokal
- Akun terkunci 15 menit setelah 5x gagal login; reset via proktor (`/proktor/siswa/{id}/unlock`)

## Cron recommendation
Tambahkan ke crontab untuk sinkronasi berkala (opsional):

```
*/30 * * * * cd /path/to/ujian-service && php artisan siakad:sync-siswa --quiet
```
