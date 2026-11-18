<?php

namespace Tests\Feature\Ujian;

use App\Modules\Ujian\Exceptions\UjianException;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\PenilaianService;
use App\Modules\Ujian\Services\SesiUjianService;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PenilaianServiceTest extends TestCase
{
    use RefreshDatabase;

    private PenilaianService $penilaian;

    private SesiUjianService $sesiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->penilaian = app(PenilaianService::class);
        $this->sesiService = app(SesiUjianService::class);
    }

    /**
     * Ujian campuran: 2 PG (bobot 2 dan 3) + 1 essay (bobot 5) = total bobot 10.
     */
    private function sesiCampuran(): SesiUjian
    {
        $ujian = Ujian::factory()->published()->create();
        SoalUjian::factory()->create(['ujian_id' => $ujian->id, 'nomor' => 1, 'kunci_jawaban' => 'A', 'bobot_nilai' => 2]);
        SoalUjian::factory()->create(['ujian_id' => $ujian->id, 'nomor' => 2, 'kunci_jawaban' => 'B', 'bobot_nilai' => 3]);
        SoalUjian::factory()->essay()->create(['ujian_id' => $ujian->id, 'nomor' => 3, 'bobot_nilai' => 5]);

        $sesi = $this->sesiService->mulai($ujian, DataSekolah::siswa($ujian->kelas));
        [$pg1, $pg2, $essay] = $ujian->soal;
        $this->sesiService->simpanJawaban($sesi, $pg1, 'A', null);  // benar: 2 poin
        $this->sesiService->simpanJawaban($sesi, $pg2, 'C', null);  // salah
        $this->sesiService->simpanJawaban($sesi, $essay, null, 'Jawaban uraian siswa');

        return $this->sesiService->selesaikan($sesi);
    }

    public function test_essay_belum_dinilai_tidak_menghitung_poin_essay(): void
    {
        $sesi = $this->sesiCampuran();

        $this->assertSame('20.00', $sesi->nilai_otomatis);
        $this->assertSame('0.00', $sesi->nilai_manual);
        $this->assertSame('20.00', $sesi->nilai_akhir);
        $this->assertTrue($this->penilaian->perluKoreksi($sesi));
    }

    public function test_nilai_essay_masuk_ke_nilai_akhir(): void
    {
        $sesi = $this->sesiCampuran();
        $jawabanEssay = $sesi->jawaban->first(fn ($j) => $j->soal->isEssay());

        $this->penilaian->nilaiEssay($jawabanEssay, 4);

        $sesi->refresh();
        $this->assertSame('20.00', $sesi->nilai_otomatis);
        $this->assertSame('40.00', $sesi->nilai_manual);
        $this->assertSame('60.00', $sesi->nilai_akhir, 'Versi lama memberi 20.00 (essay tak pernah dijumlahkan).');
        $this->assertSame('D', $sesi->predikat);
        $this->assertFalse($this->penilaian->perluKoreksi($sesi));
    }

    public function test_nilai_essay_di_luar_bobot_ditolak(): void
    {
        $sesi = $this->sesiCampuran();
        $jawabanEssay = $sesi->jawaban->first(fn ($j) => $j->soal->isEssay());

        $this->expectExceptionObject(UjianException::nilaiDiLuarRentang(5));
        $this->penilaian->nilaiEssay($jawabanEssay, 6);
    }

    public function test_jawaban_pilihan_ganda_tidak_bisa_dinilai_manual(): void
    {
        $sesi = $this->sesiCampuran();

        $this->expectExceptionObject(UjianException::bukanEssay());
        $this->penilaian->nilaiEssay($sesi->jawaban->first(), 1);
    }

    public static function predikatProvider(): array
    {
        return [[100.0, 'A'], [90.0, 'A'], [89.99, 'B'], [80.0, 'B'], [70.0, 'C'], [69.99, 'D'], [0.0, 'D'], [null, null]];
    }

    #[DataProvider('predikatProvider')]
    public function test_predikat_sama_dengan_nilai_siakad(?float $nilai, ?string $predikat): void
    {
        $this->assertSame($predikat, PenilaianService::predikat($nilai));
    }
}
