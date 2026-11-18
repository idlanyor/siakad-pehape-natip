<?php

namespace Tests\Feature\Ujian;

use App\Models\Siswa;
use App\Models\User;
use App\Modules\Ujian\Exceptions\UjianException;
use App\Modules\Ujian\Models\FasePeriode;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\SesiUjianService;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SesiUjianServiceTest extends TestCase
{
    use RefreshDatabase;

    private SesiUjianService $service;

    private Ujian $ujian;

    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SesiUjianService::class);

        $this->ujian = Ujian::factory()->published()->create(['durasi_menit' => 60]);
        foreach ([1, 2, 3] as $nomor) {
            SoalUjian::factory()->create(['ujian_id' => $this->ujian->id, 'nomor' => $nomor, 'kunci_jawaban' => 'B']);
        }
        $this->siswa = DataSekolah::siswa($this->ujian->kelas);
    }

    // --- Mulai ---------------------------------------------------------------

    public function test_mulai_membuat_sesi_dan_lembar_jawaban(): void
    {
        $sesi = $this->service->mulai($this->ujian, $this->siswa);

        $this->assertSame(SesiUjian::STATUS_BERLANGSUNG, $sesi->status);
        $this->assertSame(3600, $sesi->durasi_detik);
        $this->assertSame($this->siswa->kelas_id, $sesi->kelas_id);
        $this->assertTrue($sesi->is_resmi);
        $this->assertSame([1, 2, 3], $sesi->jawaban()->pluck('urutan')->all());
        $this->assertSame([1, 2, 3], $sesi->jawaban->map->soal->pluck('nomor')->all());
    }

    public function test_mulai_lagi_saat_berlangsung_melanjutkan_sesi_yang_sama(): void
    {
        $pertama = $this->service->mulai($this->ujian, $this->siswa);
        $kedua = $this->service->mulai($this->ujian, $this->siswa);

        $this->assertTrue($pertama->is($kedua));
        $this->assertSame(3, $kedua->jawaban()->count());
    }

    public function test_tidak_bisa_mulai_ujian_draft(): void
    {
        $this->ujian->update(['status' => Ujian::STATUS_DRAFT]);

        $this->expectExceptionObject(UjianException::belumDipublikasikan());
        $this->service->mulai($this->ujian, $this->siswa);
    }

    public function test_tidak_bisa_mulai_di_luar_jadwal(): void
    {
        $this->ujian->update(['waktu_mulai' => now()->addHour(), 'waktu_selesai' => now()->addHours(3)]);

        $this->expectExceptionObject(UjianException::diLuarJadwal());
        $this->service->mulai($this->ujian, $this->siswa);
    }

    public function test_tidak_bisa_mulai_ujian_kelas_lain(): void
    {
        $siswaLain = DataSekolah::siswa();

        $this->expectExceptionObject(UjianException::bukanKelasSiswa());
        $this->service->mulai($this->ujian, $siswaLain);
    }

    public function test_tidak_bisa_mulai_ujian_tanpa_soal(): void
    {
        $this->ujian->soal()->delete();

        $this->expectExceptionObject(UjianException::tanpaSoal());
        $this->service->mulai($this->ujian, $this->siswa);
    }

    public function test_tidak_bisa_mulai_lagi_setelah_selesai(): void
    {
        $sesi = $this->service->mulai($this->ujian, $this->siswa);
        $this->service->selesaikan($sesi);

        $this->expectExceptionObject(UjianException::sudahSelesai());
        $this->service->mulai($this->ujian, $this->siswa);
    }

    public function test_sesi_try_out_tidak_resmi(): void
    {
        FasePeriode::factory()->for($this->ujian->periode, 'periode')->tipe('try_out', 1)->create();

        $sesi = $this->service->mulai($this->ujian, $this->siswa);

        $this->assertSame('try_out', $sesi->fase);
        $this->assertFalse($sesi->is_resmi);
    }

    // --- Pengacakan ----------------------------------------------------------

    public function test_acak_soal_dan_opsi_dikunci_sekali_per_siswa(): void
    {
        $this->ujian->update(['acak_soal' => true, 'acak_opsi' => true]);
        foreach (range(4, 20) as $nomor) {
            SoalUjian::factory()->create(['ujian_id' => $this->ujian->id, 'nomor' => $nomor]);
        }

        $sesi = $this->service->mulai($this->ujian, $this->siswa);
        $urutanAwal = $sesi->jawaban()->with('soal')->get()->map(fn ($j) => [$j->soal->nomor, $j->urutan_opsi])->all();

        $this->assertNotSame(range(1, 20), array_column($urutanAwal, 0), 'Soal seharusnya teracak (peluang gagal 1/20!).');
        foreach ($urutanAwal as [, $urutanOpsi]) {
            $this->assertSame(['A', 'B', 'C', 'D'], collect(str_split($urutanOpsi))->sort()->values()->all());
        }

        // Melanjutkan sesi tidak mengacak ulang.
        $lanjut = $this->service->mulai($this->ujian, $this->siswa);
        $this->assertSame($urutanAwal, $lanjut->jawaban()->with('soal')->get()->map(fn ($j) => [$j->soal->nomor, $j->urutan_opsi])->all());
    }

    // --- Menjawab ------------------------------------------------------------

    public function test_simpan_jawaban_pilihan_ganda_dan_ragu(): void
    {
        $sesi = $this->service->mulai($this->ujian, $this->siswa);
        $soal = $this->ujian->soal->first();

        $this->service->simpanJawaban($sesi, $soal, 'c', null, ragu: true);
        $jawaban = $sesi->jawaban()->where('soal_ujian_id', $soal->id)->first();

        $this->assertSame('C', $jawaban->jawaban_pilihan);
        $this->assertTrue($jawaban->is_ragu);
        $this->assertNull($jawaban->is_benar, 'Kunci tidak boleh dievaluasi selama ujian berlangsung.');
    }

    public function test_tolak_opsi_yang_tidak_ada(): void
    {
        $sesi = $this->service->mulai($this->ujian, $this->siswa);

        $this->expectExceptionObject(UjianException::opsiTidakValid());
        $this->service->simpanJawaban($sesi, $this->ujian->soal->first(), 'E', null);
    }

    public function test_tolak_soal_dari_ujian_lain(): void
    {
        $sesi = $this->service->mulai($this->ujian, $this->siswa);
        $soalLain = SoalUjian::factory()->create();

        $this->expectExceptionObject(UjianException::soalBukanBagianSesi());
        $this->service->simpanJawaban($sesi, $soalLain, 'A', null);
    }

    public function test_jawaban_setelah_waktu_habis_ditolak_dan_sesi_timeout(): void
    {
        $sesi = $this->service->mulai($this->ujian, $this->siswa);
        $this->travel(61)->minutes();

        try {
            $this->service->simpanJawaban($sesi, $this->ujian->soal->first(), 'B', null);
            $this->fail('Seharusnya ditolak.');
        } catch (UjianException $e) {
            $this->assertSame(UjianException::waktuHabis()->getMessage(), $e->getMessage());
        }

        $this->assertSame(SesiUjian::STATUS_TIMEOUT, $sesi->fresh()->status);
        $this->assertNotNull($sesi->fresh()->nilai_akhir);
    }

    public function test_batas_waktu_tidak_melewati_akhir_jendela_ujian(): void
    {
        $this->ujian->update(['waktu_selesai' => now()->addMinutes(20)]);

        $sesi = $this->service->mulai($this->ujian, $this->siswa);

        $this->assertEqualsWithDelta(20 * 60, $this->service->sisaDetik($sesi), 2);
    }

    // --- Selesai & penilaian -------------------------------------------------

    public function test_selesaikan_menilai_otomatis(): void
    {
        $sesi = $this->service->mulai($this->ujian, $this->siswa);
        [$s1, $s2] = $this->ujian->soal;
        $this->service->simpanJawaban($sesi, $s1, 'B', null);
        $this->service->simpanJawaban($sesi, $s2, 'A', null);
        // soal ke-3 tidak dijawab

        $sesi = $this->service->selesaikan($sesi);

        $this->assertSame(SesiUjian::STATUS_SELESAI, $sesi->status);
        $this->assertNotNull($sesi->waktu_selesai);
        $this->assertSame('33.33', $sesi->nilai_akhir);
        $this->assertSame('D', $sesi->predikat);
        $this->assertSame([true, false, false], $sesi->jawaban()->pluck('is_benar')->all());
    }

    public function test_selesaikan_dua_kali_ditolak(): void
    {
        $sesi = $this->service->mulai($this->ujian, $this->siswa);
        $this->service->selesaikan($sesi);

        $this->expectExceptionObject(UjianException::sesiTidakAktif());
        $this->service->selesaikan($sesi);
    }

    // --- Pelanggaran ---------------------------------------------------------

    public function test_pelanggaran_dihitung_dan_didiskualifikasi_di_batas(): void
    {
        config(['ujian.batas_pelanggaran' => 3]);
        $sesi = $this->service->mulai($this->ujian, $this->siswa);

        $this->service->catatPelanggaran($sesi, 'tab_switch');
        $this->service->catatPelanggaran($sesi, 'reconnect'); // dicatat, tidak dihitung
        $this->service->catatPelanggaran($sesi, 'copy');
        $this->assertSame(2, $sesi->fresh()->jumlah_pelanggaran);
        $this->assertTrue($sesi->fresh()->isBerlangsung());

        $this->service->catatPelanggaran($sesi, 'devtools_open');

        $sesi->refresh();
        $this->assertSame(SesiUjian::STATUS_DISKUALIFIKASI, $sesi->status);
        $this->assertSame('0.00', $sesi->nilai_akhir);
        $this->assertSame(1, $sesi->proktorEvents()->tipe('disqualify')->count());
        $this->assertSame(4, $sesi->proktorEvents()->pelanggaran()->count());
    }

    public function test_batas_pelanggaran_nol_menonaktifkan_diskualifikasi_otomatis(): void
    {
        config(['ujian.batas_pelanggaran' => 0]);
        $sesi = $this->service->mulai($this->ujian, $this->siswa);

        foreach (range(1, 10) as $_) {
            $this->service->catatPelanggaran($sesi, 'tab_switch');
        }

        $this->assertTrue($sesi->fresh()->isBerlangsung());
        $this->assertSame(10, $sesi->fresh()->jumlah_pelanggaran);
    }

    // --- Aksi proktor --------------------------------------------------------

    public function test_paksa_selesai_menilai_dan_mencatat_pelaku(): void
    {
        $proktor = User::factory()->create();
        $sesi = $this->service->mulai($this->ujian, $this->siswa);
        $this->service->simpanJawaban($sesi, $this->ujian->soal->first(), 'B', null);

        $this->service->paksaSelesai($sesi, $proktor, 'Listrik padam');

        $this->assertSame(SesiUjian::STATUS_SELESAI, $sesi->status);
        $this->assertSame('33.33', $sesi->nilai_akhir, 'Versi lama tidak menilai saat paksa selesai.');
        $event = $sesi->proktorEvents()->tipe('force_finish')->first();
        $this->assertSame('Listrik padam', $event->keterangan);
        $this->assertTrue($event->user->is($proktor));
    }

    public function test_diskualifikasi_sesi_yang_sudah_selesai(): void
    {
        $sesi = $this->service->mulai($this->ujian, $this->siswa);
        foreach ($this->ujian->soal as $soal) {
            $this->service->simpanJawaban($sesi, $soal, 'B', null);
        }
        $this->service->selesaikan($sesi);
        $this->assertSame('100.00', $sesi->nilai_akhir);

        $this->service->diskualifikasi($sesi, User::factory()->create(), 'Menyontek');

        $this->assertSame(SesiUjian::STATUS_DISKUALIFIKASI, $sesi->status);
        $this->assertSame('0.00', $sesi->nilai_akhir);
    }

    public function test_reset_memungkinkan_siswa_mengulang(): void
    {
        $sesi = $this->service->mulai($this->ujian, $this->siswa);
        $this->service->catatPelanggaran($sesi, 'tab_switch');
        $this->service->selesaikan($sesi);

        $this->service->reset($sesi, User::factory()->create());

        $this->assertSame(SesiUjian::STATUS_BELUM, $sesi->status);
        $this->assertSame(0, $sesi->jawaban()->count());
        $this->assertNull($sesi->nilai_akhir);
        $this->assertSame(0, $sesi->jumlah_pelanggaran);
        $this->assertSame(2, $sesi->proktorEvents()->count(), 'Log pelanggaran + reset tetap tersimpan.');

        $ulang = $this->service->mulai($this->ujian, $this->siswa);
        $this->assertTrue($ulang->is($sesi));
        $this->assertTrue($ulang->isBerlangsung());
        $this->assertSame(3, $ulang->jawaban()->count());
    }
}
