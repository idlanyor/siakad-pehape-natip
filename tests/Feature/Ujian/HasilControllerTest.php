<?php

namespace Tests\Feature\Ujian;

use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\SesiUjianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Ujian\Concerns\SiapkanSekolah;
use Tests\TestCase;

class HasilControllerTest extends TestCase
{
    use RefreshDatabase, SiapkanSekolah;

    private SesiUjian $sesi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSekolah();

        $this->ujian->update(['status' => Ujian::STATUS_PUBLISHED, 'waktu_mulai' => now()->subHour(), 'waktu_selesai' => now()->addHour()]);
        $pg = SoalUjian::factory()->create(['ujian_id' => $this->ujian->id, 'nomor' => 1, 'kunci_jawaban' => 'A', 'bobot_nilai' => 5]);
        $essay = SoalUjian::factory()->essay()->create(['ujian_id' => $this->ujian->id, 'nomor' => 2, 'bobot_nilai' => 5]);

        $service = app(SesiUjianService::class);
        $this->sesi = $service->mulai($this->ujian, $this->siswa);
        $service->simpanJawaban($this->sesi, $pg, 'A', null);
        $service->simpanJawaban($this->sesi, $essay, null, 'Uraian');
        $service->selesaikan($this->sesi);
    }

    public function test_rekap_hasil_dan_statistik(): void
    {
        $this->actingAs($this->guru->user)
            ->get(route('ujian.hasil.index', $this->ujian))
            ->assertInertia(fn (Assert $p) => $p->component('Hasil/Index')
                ->has('sesi', 1)
                ->where('sesi.0.nilai_akhir', '50.00')
                ->where('sesi.0.perlu_koreksi', true)
                ->where('statistik.perlu_koreksi', 1)
                ->where('statistik.lulus', 0));
    }

    public function test_koreksi_essay_memperbarui_nilai_akhir(): void
    {
        $jawabanEssay = $this->sesi->jawaban()->whereHas('soal', fn ($q) => $q->where('tipe', 'essay'))->first();

        $this->actingAs($this->guru->user)
            ->put(route('ujian.hasil.nilai-essay', $jawabanEssay), ['nilai' => 6])
            ->assertSessionHas('error', 'Nilai harus di antara 0 dan 5.');

        $this->actingAs($this->guru->user)
            ->put(route('ujian.hasil.nilai-essay', $jawabanEssay), ['nilai' => 4])
            ->assertSessionHas('success');

        $this->assertSame('90.00', $this->sesi->fresh()->nilai_akhir);
        $this->assertSame('A', $this->sesi->fresh()->predikat);
    }

    public function test_detail_sesi_menampilkan_kunci_untuk_guru(): void
    {
        $this->actingAs($this->guru->user)
            ->get(route('ujian.hasil.sesi', $this->sesi))
            ->assertInertia(fn (Assert $p) => $p->component('Hasil/Sesi')
                ->has('jawaban', 2)
                ->where('jawaban.0.kunci_jawaban', 'A')
                ->where('jawaban.0.is_benar', true));
    }

    public function test_hanya_pemilik_dan_admin_yang_melihat_hasil(): void
    {
        $this->ujian->pengawas()->attach($this->guruLain);

        $this->actingAs($this->guruLain->user)->get(route('ujian.hasil.index', $this->ujian))->assertForbidden();
        $this->actingAs($this->guruLain->user)->get(route('ujian.hasil.sesi', $this->sesi))->assertForbidden();
        $this->actingAs($this->siswa->user)->get(route('ujian.hasil.sesi', $this->sesi))->assertForbidden();
        $this->actingAs($this->admin)->get(route('ujian.hasil.index', $this->ujian))->assertOk();
    }

    public function test_export_excel_dan_pdf(): void
    {
        $this->actingAs($this->guru->user)
            ->get(route('ujian.hasil.excel', $this->ujian))
            ->assertOk()
            ->assertDownload();

        $this->actingAs($this->guru->user)
            ->get(route('ujian.hasil.pdf', $this->ujian))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
