<?php

namespace App\Services;

use App\Models\Materi;
use App\Models\Notifikasi;
use App\Models\PengumpulanTugas;
use App\Models\SesiUjian;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Models\Ujian;
use Illuminate\Support\Str;

/**
 * Fan-out notifikasi in-app untuk event e-learning. Dipanggil dari model events
 * (Materi/Tugas/PengumpulanTugas) agar berlaku baik dari web maupun REST API.
 */
class ELearningNotifier
{
    /** Siswa di kelas pada satu jadwal → ambil user_id (skip yg null). */
    private static function siswaUserIdsByJadwal(?int $kelasId): array
    {
        if (! $kelasId) {
            return [];
        }

        return Siswa::where('kelas_id', $kelasId)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->all();
    }

    /** Bulk-insert notifikasi (1 query) dengan timestamp manual. */
    private static function fanOut(array $userIds, string $judul, string $pesan, ?string $url): void
    {
        if (empty($userIds)) {
            return;
        }

        $now  = now();
        $rows = array_map(fn ($uid) => [
            'user_id'    => $uid,
            'judul'      => $judul,
            'pesan'      => $pesan,
            'url'        => $url,
            'created_at' => $now,
            'updated_at' => $now,
        ], $userIds);

        Notifikasi::insert($rows);
    }

    public static function materiTerbit(Materi $materi): void
    {
        if (! $materi->is_published) {
            return;
        }

        $jadwal = $materi->jadwal;
        $mapel  = $jadwal?->mapel?->nama_mapel ?? 'Mapel';

        self::fanOut(
            self::siswaUserIdsByJadwal($jadwal?->kelas_id),
            'Materi baru: ' . $materi->judul,
            Str::limit($mapel . ' — ' . ($materi->deskripsi ?: 'Materi baru telah ditambahkan.'), 120),
            route('siswa.materi.detail', $materi->id),
        );
    }

    public static function tugasBaru(Tugas $tugas): void
    {
        $jadwal = $tugas->jadwal;
        $mapel  = $jadwal?->mapel?->nama_mapel ?? 'Mapel';

        self::fanOut(
            self::siswaUserIdsByJadwal($jadwal?->kelas_id),
            'Tugas baru: ' . $tugas->judul,
            Str::limit($mapel . ' — deadline ' . optional($tugas->deadline)->format('d/m/Y H:i'), 120),
            route('siswa.tugas.show', $tugas->id),
        );
    }

    public static function tugasDinilai(PengumpulanTugas $pengumpulan): void
    {
        $siswa = $pengumpulan->siswa;
        if (! $siswa || ! $siswa->user_id) {
            return;
        }

        $tugas = $pengumpulan->tugas;

        self::fanOut(
            [$siswa->user_id],
            'Tugas dinilai: ' . ($tugas?->judul ?? 'Tugas'),
            'Nilai kamu: ' . rtrim(rtrim((string) $pengumpulan->nilai, '0'), '.')
                . ' / ' . ($tugas?->nilai_maksimal ?? 100),
            route('siswa.tugas.show', $tugas?->id),
        );
    }

    public static function ujianBaru(Ujian $ujian): void
    {
        if (! $ujian->is_published) {
            return;
        }

        $jadwal = $ujian->jadwal;
        $mapel  = $jadwal?->mapel?->nama_mapel ?? 'Mapel';

        self::fanOut(
            self::siswaUserIdsByJadwal($jadwal?->kelas_id),
            'Ujian baru: ' . $ujian->judul,
            Str::limit($mapel . ' — ' . $ujian->tipe_label . ' | ' . $ujian->waktu_mulai->format('d/m/Y H:i'), 120),
            route('ujian.sso'),
        );
    }

    public static function ujianReminder(Ujian $ujian): void
    {
        if (! $ujian->is_published) {
            return;
        }

        $jadwal = $ujian->jadwal;
        $mapel  = $jadwal?->mapel?->nama_mapel ?? 'Mapel';

        self::fanOut(
            self::siswaUserIdsByJadwal($jadwal?->kelas_id),
            'Reminder: Ujian besok',
            Str::limit($ujian->judul . ' — ' . $mapel . ' | ' . $ujian->waktu_mulai->format('d/m/Y H:i'), 120),
            route('ujian.sso'),
        );
    }

    public static function ujianDikoreksi(SesiUjian $sesi): void
    {
        $siswa = $sesi->siswa;
        if (! $siswa || ! $siswa->user_id) {
            return;
        }

        $ujian = $sesi->ujian;

        self::fanOut(
            [$siswa->user_id],
            'Ujian dikoreksi: ' . ($ujian?->judul ?? 'Ujian'),
            'Nilai kamu: ' . number_format($sesi->nilai_akhir, 2) . ' — ' . $sesi->predikat,
            route('ujian.sso'),
        );
    }
}
