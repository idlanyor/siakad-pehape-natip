<?php

namespace App\Services;

use App\Models\Materi;
use App\Models\Notifikasi;
use App\Models\PengumpulanTugas;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\Ujian;
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

        $now = now();
        $rows = array_map(fn ($uid) => [
            'user_id' => $uid,
            'judul' => $judul,
            'pesan' => $pesan,
            'url' => $url,
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
        $mapel = $jadwal?->mapel?->nama_mapel ?? 'Mapel';

        self::fanOut(
            self::siswaUserIdsByJadwal($jadwal?->kelas_id),
            'Materi baru: '.$materi->judul,
            Str::limit($mapel.' — '.($materi->deskripsi ?: 'Materi baru telah ditambahkan.'), 120),
            route('siswa.materi.detail', $materi->id),
        );
    }

    public static function tugasBaru(Tugas $tugas): void
    {
        $jadwal = $tugas->jadwal;
        $mapel = $jadwal?->mapel?->nama_mapel ?? 'Mapel';

        self::fanOut(
            self::siswaUserIdsByJadwal($jadwal?->kelas_id),
            'Tugas baru: '.$tugas->judul,
            Str::limit($mapel.' — deadline '.optional($tugas->deadline)->format('d/m/Y H:i'), 120),
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
            'Tugas dinilai: '.($tugas?->judul ?? 'Tugas'),
            'Nilai kamu: '.rtrim(rtrim((string) $pengumpulan->nilai, '0'), '.')
                .' / '.($tugas?->nilai_maksimal ?? 100),
            route('siswa.tugas.show', $tugas?->id),
        );
    }

    /** Dipanggil saat ujian diterbitkan: semua siswa di kelas ujian. */
    public static function ujianBaru(Ujian $ujian): void
    {
        self::fanOut(
            self::siswaUserIdsByJadwal($ujian->kelas_id),
            'Ujian baru: '.$ujian->judul,
            Str::limit(($ujian->mapel?->nama_mapel ?? 'Mapel').' — '.strtoupper($ujian->tipe).' | '.$ujian->waktu_mulai->format('d/m/Y H:i'), 120),
            route('ujian.siswa.index'),
        );
    }

    /** Dipanggil saat essay terakhir sesi selesai dikoreksi guru. */
    public static function ujianDikoreksi(SesiUjian $sesi): void
    {
        $userId = $sesi->siswa?->user_id;
        if (! $userId || ! $sesi->ujian?->tampilkan_nilai) {
            return;
        }

        self::fanOut(
            [$userId],
            'Ujian dikoreksi: '.$sesi->ujian->judul,
            'Nilai kamu: '.number_format((float) $sesi->nilai_akhir, 2).' — '.$sesi->predikat,
            route('ujian.siswa.hasil', $sesi),
        );
    }
}
