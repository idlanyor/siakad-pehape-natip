<?php

namespace Tests\Feature\Ujian;

use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Ujian\Concerns\SiapkanSekolah;
use Tests\TestCase;

class UjianControllerTest extends TestCase
{
    use RefreshDatabase, SiapkanSekolah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSekolah();
    }

    public function test_guru_membuat_ujian_dan_otomatis_menjadi_pemilik(): void
    {
        $this->actingAs($this->guru->user)
            ->post(route('ujian.ujian.store'), $this->dataUjian(['guru_id' => $this->guruLain->id]))
            ->assertSessionHasNoErrors();

        $ujian = Ujian::latest('id')->first();
        $this->assertSame($this->guru->id, $ujian->guru_id, 'guru_id dari request diabaikan untuk guru.');
        $this->assertSame(Ujian::STATUS_DRAFT, $ujian->status);
        $this->assertTrue($ujian->acak_soal);
        $this->assertFalse($ujian->acak_opsi);
    }

    public function test_admin_memilih_guru_pemilik(): void
    {
        $this->actingAs($this->admin)
            ->post(route('ujian.ujian.store'), $this->dataUjian(['guru_id' => $this->guruLain->id]))
            ->assertSessionHasNoErrors();

        $this->assertSame($this->guruLain->id, Ujian::latest('id')->first()->guru_id);
    }

    public function test_validasi_mapel_dan_durasi(): void
    {
        $this->actingAs($this->guru->user)
            ->post(route('ujian.ujian.store'), $this->dataUjian(['mapel_id' => DataSekolah::mapel()->id]))
            ->assertSessionHasErrors('mapel_id');

        $this->actingAs($this->guru->user)
            ->post(route('ujian.ujian.store'), $this->dataUjian(['durasi_menit' => 150]))
            ->assertSessionHasErrors('durasi_menit');

        $this->periode->update(['status' => 'selesai']);
        $this->actingAs($this->guru->user)
            ->post(route('ujian.ujian.store'), $this->dataUjian())
            ->assertSessionHasErrors('periode_id');
    }

    public function test_guru_hanya_melihat_ujiannya(): void
    {
        Ujian::factory()->create(['guru_id' => $this->guruLain->id]);

        $this->actingAs($this->guru->user)
            ->get(route('ujian.ujian.index'))
            ->assertInertia(fn (Assert $p) => $p->component('Ujian/Index')->has('ujian.data', 1)
                ->where('ujian.data.0.id', $this->ujian->id));

        $this->actingAs($this->guruLain->user)->get(route('ujian.ujian.show', $this->ujian))->assertForbidden();
        $this->actingAs($this->guruLain->user)->put(route('ujian.ujian.update', $this->ujian), $this->dataUjian())->assertForbidden();
        $this->actingAs($this->siswa->user)->get(route('ujian.ujian.index'))->assertForbidden();
    }

    public function test_publikasi_butuh_soal_dengan_kunci_valid(): void
    {
        $kirim = fn () => $this->actingAs($this->guru->user)->post(route('ujian.ujian.status', $this->ujian), ['status' => 'published']);

        $kirim()->assertSessionHas('error', 'Tambahkan minimal satu soal sebelum mempublikasikan ujian.');

        SoalUjian::factory()->create(['ujian_id' => $this->ujian->id, 'nomor' => 1, 'kunci_jawaban' => 'E']);
        $kirim()->assertSessionHas('error');
        $this->assertSame(Ujian::STATUS_DRAFT, $this->ujian->fresh()->status);

        SoalUjian::query()->update(['kunci_jawaban' => 'B']);
        $kirim()->assertSessionHas('success');
        $this->assertSame(Ujian::STATUS_PUBLISHED, $this->ujian->fresh()->status);
    }

    public function test_tidak_bisa_kembali_ke_draft_setelah_dikerjakan(): void
    {
        $this->ujian->update(['status' => Ujian::STATUS_PUBLISHED]);
        SesiUjian::factory()->create(['ujian_id' => $this->ujian->id, 'siswa_id' => $this->siswa->id]);

        $this->actingAs($this->guru->user)
            ->post(route('ujian.ujian.status', $this->ujian), ['status' => 'draft'])
            ->assertSessionHas('error');

        $this->actingAs($this->guru->user)->delete(route('ujian.ujian.destroy', $this->ujian))->assertForbidden();
    }

    public function test_admin_menugaskan_pengawas(): void
    {
        $this->actingAs($this->guru->user)
            ->put(route('ujian.ujian.pengawas', $this->ujian), ['guru_ids' => [$this->guruLain->id]])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->put(route('ujian.ujian.pengawas', $this->ujian), ['guru_ids' => [$this->guruLain->id]])
            ->assertSessionHas('success');

        $pengawas = $this->guruLain->user;
        $this->assertTrue($pengawas->can('view', $this->ujian));
        $this->assertTrue($pengawas->can('awasi', $this->ujian));
        $this->assertFalse($pengawas->can('update', $this->ujian));
        $this->assertFalse($pengawas->can('nilai', $this->ujian));

        $this->actingAs($pengawas)
            ->get(route('ujian.ujian.show', $this->ujian))
            ->assertInertia(fn (Assert $p) => $p->component('Ujian/Show')
                ->where('izin.awasi', true)->where('izin.ubah', false)->where('ujian.pengawas.0.id', $this->guruLain->id));

        $this->actingAs($this->admin)
            ->put(route('ujian.ujian.pengawas', $this->ujian), ['guru_ids' => []])
            ->assertSessionHas('success');
        $this->assertFalse($pengawas->can('awasi', $this->ujian->fresh()));
    }

    public function test_dashboard_menghitung_ujian_guru_termasuk_yang_diawasi(): void
    {
        $diawasi = Ujian::factory()->published()->create(['guru_id' => $this->guruLain->id]);
        $diawasi->pengawas()->attach($this->guru);
        Ujian::factory()->published()->create(['guru_id' => $this->guruLain->id]);

        $this->actingAs($this->guru->user)
            ->get(route('ujian.dashboard'))
            ->assertInertia(fn (Assert $p) => $p->component('Dashboard')
                ->where('statistik.draft', 1)
                ->where('statistik.terbit', 1)
                ->has('mendatang', 2));
    }
}
