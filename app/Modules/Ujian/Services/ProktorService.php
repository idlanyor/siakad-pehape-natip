<?php

namespace App\Modules\Ujian\Services;

use App\Models\Siswa;
use App\Modules\Ujian\Models\ProktorEvent;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\Ujian;
use Illuminate\Support\Collection;

/**
 * Data monitoring proktor untuk satu ujian.
 */
class ProktorService
{
    public function __construct(private readonly SesiUjianService $sesiService) {}

    /**
     * Seluruh siswa di kelas ujian beserta status sesinya (termasuk yang belum mulai).
     *
     * @return Collection<int, array>
     */
    public function peserta(Ujian $ujian): Collection
    {
        $sesiPerSiswa = $ujian->sesi()
            ->withCount([
                // Dikelompokkan: OR tanpa grup akan lepas dari korelasi sesi_ujian_id.
                'jawaban as terjawab_count' => fn ($q) => $q->where(fn ($w) => $w
                    ->whereNotNull('jawaban_pilihan')
                    ->orWhere(fn ($t) => $t->whereNotNull('jawaban_text')->where('jawaban_text', '!=', ''))),
                'jawaban as total_soal_count',
            ])
            ->get()
            ->each(fn (SesiUjian $sesi) => $sesi->setRelation('ujian', $ujian))
            ->keyBy('siswa_id');

        $siswaKelas = Siswa::query()
            ->where('kelas_id', $ujian->kelas_id)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nisn', 'kelas_id']);

        // Siswa yang sudah pindah kelas setelah mengerjakan tetap ditampilkan.
        $siswaLain = Siswa::query()
            ->whereIn('id', $sesiPerSiswa->keys()->diff($siswaKelas->pluck('id')))
            ->get(['id', 'nama', 'nisn', 'kelas_id']);

        return $siswaKelas->concat($siswaLain)->map(function (Siswa $siswa) use ($sesiPerSiswa) {
            $sesi = $sesiPerSiswa->get($siswa->id);

            return [
                'siswa' => ['id' => $siswa->id, 'nama' => $siswa->nama, 'nisn' => $siswa->nisn],
                'sesi' => $sesi ? $this->ringkasSesi($sesi) : null,
            ];
        })->values();
    }

    public function statistik(Ujian $ujian): array
    {
        $jumlahSiswa = Siswa::where('kelas_id', $ujian->kelas_id)->count();
        $perStatus = $ujian->sesi()->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');
        $sudahMulai = (int) $perStatus->sum();

        return [
            'peserta' => $jumlahSiswa,
            'belum_mulai' => max(0, $jumlahSiswa - $sudahMulai) + (int) ($perStatus[SesiUjian::STATUS_BELUM] ?? 0),
            'berlangsung' => (int) ($perStatus[SesiUjian::STATUS_BERLANGSUNG] ?? 0),
            'selesai' => (int) ($perStatus[SesiUjian::STATUS_SELESAI] ?? 0) + (int) ($perStatus[SesiUjian::STATUS_TIMEOUT] ?? 0),
            'diskualifikasi' => (int) ($perStatus[SesiUjian::STATUS_DISKUALIFIKASI] ?? 0),
            'total_pelanggaran' => (int) $ujian->sesi()->sum('jumlah_pelanggaran'),
        ];
    }

    /**
     * @return Collection<int, array>
     */
    public function eventTerbaru(Ujian $ujian, int $limit = 50): Collection
    {
        return ProktorEvent::query()
            ->whereHas('sesi', fn ($q) => $q->where('ujian_id', $ujian->id))
            ->with(['sesi.siswa:id,nama,nisn', 'user:id,name'])
            ->latest('created_at')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (ProktorEvent $e) => [
                'id' => $e->id,
                'sesi_id' => $e->sesi_ujian_id,
                'siswa' => $e->sesi->siswa?->nama,
                'tipe' => $e->tipe,
                'label' => $e->tipe_label,
                'severity' => $e->severity,
                'keterangan' => $e->keterangan,
                'oleh' => $e->user?->name,
                'waktu' => $e->created_at,
            ]);
    }

    /**
     * online / idle / offline berdasarkan aktivitas terakhir.
     */
    public function statusKoneksi(SesiUjian $sesi): string
    {
        if (! $sesi->isBerlangsung() || $sesi->last_activity_at === null) {
            return 'offline';
        }

        $detik = $sesi->last_activity_at->diffInSeconds(now());

        return match (true) {
            $detik <= config('ujian.koneksi.online') => 'online',
            $detik <= config('ujian.koneksi.idle') => 'idle',
            default => 'offline',
        };
    }

    private function ringkasSesi(SesiUjian $sesi): array
    {
        return [
            'id' => $sesi->id,
            'status' => $sesi->status,
            'waktu_mulai' => $sesi->waktu_mulai,
            'waktu_selesai' => $sesi->waktu_selesai,
            'sisa_detik' => $sesi->isBerlangsung() ? $this->sesiService->sisaDetik($sesi) : 0,
            'terjawab' => (int) $sesi->terjawab_count,
            'total_soal' => (int) $sesi->total_soal_count,
            'jumlah_pelanggaran' => $sesi->jumlah_pelanggaran,
            'nilai_akhir' => $sesi->nilai_akhir,
            'koneksi' => $this->statusKoneksi($sesi),
        ];
    }
}
