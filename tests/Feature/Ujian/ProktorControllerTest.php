<?php

namespace Tests\Feature\Ujian;

use App\Modules\Ujian\Http\Middleware\HandleInertiaRequests;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\SesiUjianService;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Ujian\Concerns\SiapkanSekolah;
use Tests\TestCase;

class ProktorControllerTest extends TestCase
{
    use RefreshDatabase, SiapkanSekolah;

    private SesiUjian $sesi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSekolah();

        $this->ujian->update(['status' => Ujian::STATUS_PUBLISHED, 'waktu_mulai' => now()->subMinutes(10), 'waktu_selesai' => now()->addHour()]);
        $soal = SoalUjian::factory()->create(['ujian_id' => $this->ujian->id, 'nomor' => 1, 'kunci_jawaban' => 'A']);
        $service = app(SesiUjianService::class);
        $this->sesi = $service->mulai($this->ujian, $this->siswa);
        $service->simpanJawaban($this->sesi, $soal, 'A', null);
        $service->catatPelanggaran($this->sesi, 'tab_switch');
        DataSekolah::siswa($this->kelas); // belum mulai
    }

    public function test_monitor_menampilkan_peserta_statistik_dan_event(): void
    {
        $this->actingAs($this->guru->user)
            ->get(route('ujian.proktor.monitor', $this->ujian))
            ->assertInertia(fn (Assert $p) => $p->component('Proktor/Monitor')
                ->has('peserta', 2)
                ->where('statistik.berlangsung', 1)
                ->where('statistik.belum_mulai', 1)
                ->where('statistik.total_pelanggaran', 1)
                ->where('event.0.tipe', 'tab_switch')
                ->where('batasPelanggaran', 5));
    }

    public function test_polling_hanya_memuat_ulang_data_dinamis(): void
    {
        $versi = app(HandleInertiaRequests::class)->version(request());

        $this->actingAs($this->guru->user)
            ->get(route('ujian.proktor.monitor', $this->ujian), [
                'X-Inertia' => 'true',
                'X-Inertia-Version' => (string) $versi,
                'X-Inertia-Partial-Component' => 'Proktor/Monitor',
                'X-Inertia-Partial-Data' => 'peserta,statistik',
            ])
            ->assertOk()
            ->assertJsonPath('props.statistik.berlangsung', 1)
            ->assertJsonMissingPath('props.ujian')
            ->assertJsonMissingPath('props.event');
    }

    public function test_akses_monitor(): void
    {
        $this->actingAs($this->guruLain->user)->get(route('ujian.proktor.monitor', $this->ujian))->assertForbidden();
        $this->actingAs($this->siswa->user)->get(route('ujian.proktor.monitor', $this->ujian))->assertForbidden();
        $this->actingAs($this->siswa->user)->get(route('ujian.proktor.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('ujian.proktor.monitor', $this->ujian))->assertOk();

        $this->ujian->pengawas()->attach($this->guruLain);
        $this->actingAs($this->guruLain->user)->get(route('ujian.proktor.monitor', $this->ujian))->assertOk();
    }

    public function test_daftar_pengawasan_berisi_ujian_milik_dan_yang_diawasi(): void
    {
        $diawasi = Ujian::factory()->published()->create(['guru_id' => $this->guruLain->id]);
        $diawasi->pengawas()->attach($this->guru);
        Ujian::factory()->published()->create(['guru_id' => $this->guruLain->id]); // bukan urusan guru ini
        Ujian::factory()->create(['guru_id' => $this->guru->id]); // draft tidak diawasi

        $this->actingAs($this->guru->user)
            ->get(route('ujian.proktor.index'))
            ->assertInertia(fn (Assert $p) => $p->component('Proktor/Index')
                ->where('ujian', fn ($daftar) => collect($daftar)->pluck('berlangsung', 'id')->sortKeys()->all()
                    === collect([$this->ujian->id => 1, $diawasi->id => 0])->sortKeys()->all()));
    }

    public function test_pengawas_memaksa_selesai(): void
    {
        $this->ujian->pengawas()->attach($this->guruLain);

        $this->actingAs($this->guruLain->user)
            ->post(route('ujian.proktor.paksa-selesai', $this->sesi), ['alasan' => 'Listrik padam'])
            ->assertSessionHas('success');

        $sesi = $this->sesi->fresh();
        $this->assertSame(SesiUjian::STATUS_SELESAI, $sesi->status);
        $this->assertSame('100.00', $sesi->nilai_akhir);
        $event = $sesi->proktorEvents()->tipe('force_finish')->sole();
        $this->assertSame($this->guruLain->user_id, $event->user_id);
        $this->assertSame('Listrik padam', $event->keterangan);
    }

    public function test_diskualifikasi_wajib_alasan(): void
    {
        $this->actingAs($this->guru->user)
            ->post(route('ujian.proktor.diskualifikasi', $this->sesi), ['alasan' => ''])
            ->assertSessionHasErrors('alasan');

        $this->actingAs($this->guru->user)
            ->post(route('ujian.proktor.diskualifikasi', $this->sesi), ['alasan' => 'Membuka contekan'])
            ->assertSessionHas('success');

        $this->assertSame(SesiUjian::STATUS_DISKUALIFIKASI, $this->sesi->fresh()->status);
        $this->assertSame('0.00', $this->sesi->fresh()->nilai_akhir);
    }

    public function test_reset_sesi(): void
    {
        $this->actingAs($this->guru->user)
            ->post(route('ujian.proktor.reset', $this->sesi))
            ->assertSessionHas('success');

        $this->assertSame(SesiUjian::STATUS_BELUM, $this->sesi->fresh()->status);
        $this->assertSame(0, $this->sesi->jawaban()->count());
    }

    public function test_paksa_selesai_sesi_yang_sudah_selesai_ditolak(): void
    {
        app(SesiUjianService::class)->selesaikan($this->sesi);

        $this->actingAs($this->guru->user)
            ->post(route('ujian.proktor.paksa-selesai', $this->sesi))
            ->assertSessionHas('error', 'Sesi ujian tidak sedang berlangsung.');
    }

    public function test_guru_lain_tidak_bisa_melakukan_aksi(): void
    {
        foreach (['paksa-selesai', 'diskualifikasi', 'reset'] as $aksi) {
            $this->actingAs($this->guruLain->user)
                ->post(route("ujian.proktor.{$aksi}", $this->sesi), ['alasan' => 'x'])
                ->assertForbidden();
        }
        $this->assertTrue($this->sesi->fresh()->isBerlangsung());
    }
}
