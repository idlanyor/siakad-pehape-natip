<?php

namespace App\Modules\Ujian\Services;

use App\Models\Siswa;
use App\Models\User;
use App\Modules\Ujian\Exceptions\UjianException;
use App\Modules\Ujian\Models\JawabanSiswa;
use App\Modules\Ujian\Models\ProktorEvent;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Siklus hidup sesi ujian siswa: mulai, menjawab, selesai, timeout,
 * pelanggaran, dan aksi proktor (paksa selesai, diskualifikasi, reset).
 */
class SesiUjianService
{
    public function __construct(private readonly PenilaianService $penilaian) {}

    /**
     * Mulai (atau lanjutkan) sesi ujian untuk siswa.
     *
     * Sesi yang masih berlangsung dikembalikan apa adanya, sehingga siswa yang
     * me-refresh halaman atau pindah perangkat bisa melanjutkan.
     */
    public function mulai(Ujian $ujian, Siswa $siswa): SesiUjian
    {
        if (! $ujian->isPublished()) {
            throw UjianException::belumDipublikasikan();
        }
        if (! $ujian->isDibuka()) {
            throw UjianException::diLuarJadwal();
        }
        if ((int) $siswa->kelas_id !== (int) $ujian->kelas_id) {
            throw UjianException::bukanKelasSiswa();
        }

        return DB::transaction(function () use ($ujian, $siswa) {
            $sesi = SesiUjian::query()
                ->where('ujian_id', $ujian->id)
                ->where('siswa_id', $siswa->id)
                ->lockForUpdate()
                ->first();

            if ($sesi?->isBerlangsung() && ! $this->periksaWaktu($sesi)) {
                return $sesi;
            }
            if ($sesi?->isBerakhir()) {
                throw UjianException::sudahSelesai();
            }

            $soal = $ujian->soal()->get();
            if ($soal->isEmpty()) {
                throw UjianException::tanpaSoal();
            }

            $fase = $this->faseSesi($ujian);

            // $sesi di sini null (belum pernah mulai) atau berstatus "belum" (setelah direset).
            $sesi ??= new SesiUjian(['ujian_id' => $ujian->id, 'siswa_id' => $siswa->id]);
            $sesi->fill([
                'kelas_id' => $siswa->kelas_id,
                'status' => SesiUjian::STATUS_BERLANGSUNG,
                'fase' => $fase,
                'is_resmi' => $fase !== 'try_out',
                'waktu_mulai' => now(),
                'waktu_selesai' => null,
                'durasi_detik' => $ujian->durasi_menit * 60,
                'last_activity_at' => now(),
            ])->save();

            $this->buatLembarJawaban($sesi, $ujian, $soal);

            return $sesi;
        });
    }

    /**
     * Batas waktu sesi: durasi ujian (+ tambahan proktor) sejak mulai, tetapi
     * tidak melewati akhir jendela ujian (+ tambahan proktor).
     */
    public function batasWaktu(SesiUjian $sesi): Carbon
    {
        $tambahan = (int) $sesi->tambahan_detik;
        $batasDurasi = $sesi->waktu_mulai->copy()->addSeconds($sesi->durasi_detik + $tambahan);

        // Waktu tambahan dari proktor ikut memperpanjang jam tutup ujian untuk sesi ini.
        return $batasDurasi->min($sesi->ujian->waktu_selesai->copy()->addSeconds($tambahan));
    }

    public function sisaDetik(SesiUjian $sesi): int
    {
        if (! $sesi->isBerlangsung()) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->batasWaktu($sesi), false));
    }

    /**
     * Akhiri sesi dengan status timeout bila waktunya sudah habis.
     *
     * @return bool true bila sesi (sekarang) sudah berakhir.
     */
    public function periksaWaktu(SesiUjian $sesi): bool
    {
        if ($sesi->isBerlangsung() && now()->greaterThanOrEqualTo($this->batasWaktu($sesi))) {
            $this->akhiri($sesi, SesiUjian::STATUS_TIMEOUT);
        }

        return $sesi->isBerakhir();
    }

    /**
     * Simpan jawaban satu soal (autosave). Pilihan ganda memakai huruf opsi asli
     * (A–E), bukan huruf tampilan setelah opsi diacak.
     */
    public function simpanJawaban(
        SesiUjian $sesi,
        SoalUjian $soal,
        ?string $pilihan,
        ?string $teks,
        bool $ragu = false,
    ): JawabanSiswa {
        $this->pastikanBerlangsung($sesi);

        $jawaban = $sesi->jawaban()->where('soal_ujian_id', $soal->id)->first()
            ?? throw UjianException::soalBukanBagianSesi();

        if ($soal->isPilihanGanda()) {
            $pilihan = $pilihan === null || $pilihan === '' ? null : strtoupper($pilihan);
            if ($pilihan !== null && ! array_key_exists($pilihan, $soal->opsi())) {
                throw UjianException::opsiTidakValid();
            }
            $jawaban->fill(['jawaban_pilihan' => $pilihan]);
        } else {
            $jawaban->fill(['jawaban_text' => $teks]);
        }

        $jawaban->fill(['is_ragu' => $ragu])->save();
        $sesi->update(['last_activity_at' => now()]);

        return $jawaban;
    }

    /**
     * Siswa menyelesaikan ujian.
     */
    public function selesaikan(SesiUjian $sesi): SesiUjian
    {
        $this->pastikanBerlangsung($sesi);

        return $this->akhiri($sesi, SesiUjian::STATUS_SELESAI);
    }

    /**
     * Catat pelanggaran dari browser siswa. Sesi otomatis didiskualifikasi
     * setelah mencapai config('ujian.batas_pelanggaran').
     * Event "reconnect" dicatat tetapi tidak dihitung sebagai pelanggaran.
     */
    public function catatPelanggaran(SesiUjian $sesi, string $tipe, array $detail = []): ProktorEvent
    {
        $event = $sesi->proktorEvents()->create([
            'tipe' => $tipe,
            'detail' => $detail ?: null,
        ]);

        if (! $sesi->isBerlangsung() || $tipe === 'reconnect') {
            return $event;
        }

        $sesi->increment('jumlah_pelanggaran', 1, ['last_activity_at' => now()]);

        $batas = (int) config('ujian.batas_pelanggaran');
        if ($batas > 0 && $sesi->jumlah_pelanggaran >= $batas) {
            $this->akhiri($sesi, SesiUjian::STATUS_DISKUALIFIKASI);
            $sesi->proktorEvents()->create([
                'tipe' => 'disqualify',
                'keterangan' => "Otomatis: mencapai {$batas} pelanggaran.",
            ]);
        }

        return $event;
    }

    public function paksaSelesai(SesiUjian $sesi, User $oleh, ?string $alasan = null): SesiUjian
    {
        $this->pastikanBerlangsung($sesi);

        return DB::transaction(function () use ($sesi, $oleh, $alasan) {
            $this->akhiri($sesi, SesiUjian::STATUS_SELESAI);
            $this->logAksi($sesi, 'force_finish', $oleh, $alasan ?? 'Sesi dipaksa selesai oleh proktor.');

            return $sesi;
        });
    }

    public function diskualifikasi(SesiUjian $sesi, User $oleh, string $alasan): SesiUjian
    {
        return DB::transaction(function () use ($sesi, $oleh, $alasan) {
            $this->akhiri($sesi, SesiUjian::STATUS_DISKUALIFIKASI, paksa: true);
            $this->logAksi($sesi, 'disqualify', $oleh, $alasan);

            return $sesi;
        });
    }

    /**
     * Proktor menambah waktu satu sesi yang sedang berlangsung.
     */
    public function tambahWaktu(SesiUjian $sesi, int $menit, User $oleh, ?string $alasan = null): SesiUjian
    {
        if ($menit < 1 || $menit > 180) {
            throw UjianException::menitTidakValid();
        }
        $this->pastikanBerlangsung($sesi);

        return DB::transaction(function () use ($sesi, $menit, $oleh, $alasan) {
            $sesi->increment('tambahan_detik', $menit * 60);
            $this->logAksi($sesi, 'extend_time', $oleh, "+{$menit} menit".($alasan ? ": {$alasan}" : ''));

            return $sesi;
        });
    }

    /**
     * Terbitkan token perangkat baru untuk halaman mengerjakan. Perangkat/tab
     * sebelumnya otomatis tertolak. Bila perangkat sebelumnya masih aktif
     * (2 menit terakhir), perpindahan dicatat agar terlihat oleh proktor.
     */
    public function klaimPerangkat(SesiUjian $sesi, ?string $ip = null, ?string $userAgent = null): string
    {
        $masihAktif = $sesi->token_perangkat !== null
            && $sesi->last_activity_at?->greaterThan(now()->subMinutes(2));

        $token = Str::random(40);
        $sesi->forceFill(['token_perangkat' => $token, 'last_activity_at' => now()])->save();

        if ($masihAktif) {
            $sesi->proktorEvents()->create([
                'tipe' => 'device_switch',
                'keterangan' => 'Sesi dibuka di perangkat/tab lain; perangkat sebelumnya diputus.',
                'detail' => array_filter(['ip' => $ip, 'user_agent' => $userAgent ? Str::limit($userAgent, 200) : null]),
            ]);
        }

        return $token;
    }

    public function pastikanPerangkat(SesiUjian $sesi, ?string $token): void
    {
        if ($sesi->token_perangkat !== null && ! hash_equals($sesi->token_perangkat, (string) $token)) {
            throw UjianException::perangkatLain();
        }
    }

    /**
     * Kembalikan sesi ke status "belum" sehingga siswa bisa mengulang dari awal.
     * Jawaban dihapus; log event tetap disimpan untuk audit.
     */
    public function reset(SesiUjian $sesi, User $oleh, ?string $alasan = null): SesiUjian
    {
        return DB::transaction(function () use ($sesi, $oleh, $alasan) {
            $sesi->jawaban()->delete();
            $sesi->update([
                'status' => SesiUjian::STATUS_BELUM,
                'waktu_mulai' => null,
                'waktu_selesai' => null,
                'nilai_otomatis' => null,
                'nilai_manual' => null,
                'nilai_akhir' => null,
                'predikat' => null,
                'jumlah_pelanggaran' => 0,
                'tambahan_detik' => 0,
                'token_perangkat' => null,
            ]);
            $this->logAksi($sesi, 'reset', $oleh, $alasan ?? 'Sesi direset oleh proktor.');

            return $sesi;
        });
    }

    /**
     * Tutup sesi dan nilai. Idempoten: sesi yang sudah berakhir tidak diubah
     * (kecuali $paksa, dipakai diskualifikasi oleh proktor).
     */
    private function akhiri(SesiUjian $sesi, string $status, bool $paksa = false): SesiUjian
    {
        return DB::transaction(function () use ($sesi, $status, $paksa) {
            $terkunci = SesiUjian::query()->lockForUpdate()->findOrFail($sesi->id);

            if ($terkunci->isBerakhir() && ! $paksa) {
                return $sesi->refresh();
            }

            $terkunci->update([
                'status' => $status,
                'waktu_selesai' => $terkunci->waktu_selesai ?? now(),
            ]);

            $this->penilaian->koreksiOtomatis($terkunci);

            return $sesi->refresh();
        });
    }

    private function pastikanBerlangsung(SesiUjian $sesi): void
    {
        if ($this->periksaWaktu($sesi) && $sesi->status === SesiUjian::STATUS_TIMEOUT) {
            throw UjianException::waktuHabis();
        }
        if (! $sesi->isBerlangsung()) {
            throw UjianException::sesiTidakAktif();
        }
    }

    /**
     * Fase sesi mengikuti fase aktif periode (try out tidak dihitung resmi).
     */
    private function faseSesi(Ujian $ujian): string
    {
        $fase = $ujian->periode?->fase_aktif;

        return in_array($fase, SesiUjian::FASE, true) ? $fase : 'pelaksanaan';
    }

    /**
     * Buat baris jawaban kosong untuk setiap soal, sekaligus mengunci urutan
     * soal dan opsi untuk siswa ini (diacak sekali, stabil saat reload).
     *
     * @param  Collection<int, SoalUjian>  $soal
     */
    private function buatLembarJawaban(SesiUjian $sesi, Ujian $ujian, $soal): void
    {
        $urutanSoal = $ujian->acak_soal ? $soal->shuffle() : $soal;

        $baris = $urutanSoal->values()->map(fn (SoalUjian $s, int $i) => [
            'sesi_ujian_id' => $sesi->id,
            'soal_ujian_id' => $s->id,
            'urutan' => $i + 1,
            'urutan_opsi' => $ujian->acak_opsi && $s->isPilihanGanda()
                ? collect(array_keys($s->opsi()))->shuffle()->implode('')
                : null,
            'is_ragu' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        JawabanSiswa::insert($baris->all());
    }

    private function logAksi(SesiUjian $sesi, string $tipe, User $oleh, string $keterangan): void
    {
        $sesi->proktorEvents()->create([
            'tipe' => $tipe,
            'keterangan' => $keterangan,
            'user_id' => $oleh->id,
        ]);
    }
}
