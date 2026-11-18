<?php

namespace Tests\Feature\Ujian;

use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\SesiUjianService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Ujian\Concerns\SiapkanSekolah;
use Tests\TestCase;

class SidebarUjianTest extends TestCase
{
    use RefreshDatabase, SiapkanSekolah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSekolah();
    }

    /**
     * Regresi: sidebar guru memanggil route('guru.ujian.index') yang sudah
     * dihapus, sehingga semua halaman guru error 500.
     */
    public function test_halaman_siakad_guru_tidak_error_dan_menampilkan_menu_ujian(): void
    {
        $this->actingAs($this->guru->user)
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertSee(route('ujian.ujian.index'), false)
            ->assertSee('Ujian Saya');
    }

    public function test_badge_jumlah_essay_yang_perlu_dikoreksi(): void
    {
        $this->ujian->update(['status' => Ujian::STATUS_PUBLISHED, 'waktu_mulai' => now()->subHour(), 'waktu_selesai' => now()->addHour()]);
        SoalUjian::factory()->essay()->create(['ujian_id' => $this->ujian->id, 'nomor' => 1]);
        $service = app(SesiUjianService::class);
        $service->selesaikan($service->mulai($this->ujian, $this->siswa));

        $this->actingAs($this->guru->user)
            ->get(route('guru.dashboard'))
            ->assertSee('title="Jawaban essay belum dinilai">1</span>', false);
    }

    public function test_menu_siswa_menuju_modul_ujian(): void
    {
        $this->actingAs($this->siswa->user)
            ->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSee(route('ujian.siswa.index'), false);
    }
}
