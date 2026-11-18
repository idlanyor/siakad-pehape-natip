<?php

namespace Tests\Feature\Ujian;

use App\Modules\Ujian\Models\FasePeriode;
use App\Modules\Ujian\Models\PeriodeUjian;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Ujian\Concerns\SiapkanSekolah;
use Tests\TestCase;

class PeriodeFaseControllerTest extends TestCase
{
    use RefreshDatabase, SiapkanSekolah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSekolah();
    }

    public function test_admin_membuat_periode(): void
    {
        $semester = DataSekolah::semester();

        $this->actingAs($this->admin)
            ->post(route('ujian.periode.store'), ['nama' => 'UTS Genap', 'semester_id' => $semester->id, 'status' => 'aktif'])
            ->assertRedirect(route('ujian.periode.show', PeriodeUjian::latest('id')->first()))
            ->assertSessionHas('success');
    }

    public function test_guru_hanya_melihat_periode(): void
    {
        $this->actingAs($this->guru->user)
            ->get(route('ujian.periode.index'))
            ->assertInertia(fn (Assert $p) => $p->component('Periode/Index')->where('bolehKelola', false)->has('periode.data', 1));

        $this->actingAs($this->guru->user)
            ->post(route('ujian.periode.store'), ['nama' => 'x', 'semester_id' => $this->periode->semester_id, 'status' => 'draft'])
            ->assertForbidden();
    }

    public function test_siswa_tidak_bisa_membuka_periode(): void
    {
        $this->actingAs($this->siswa->user)->get(route('ujian.periode.index'))->assertForbidden();
    }

    public function test_kelola_fase_dan_override(): void
    {
        $this->actingAs($this->admin)
            ->post(route('ujian.fase.store', $this->periode), [
                'tipe' => 'pelaksanaan', 'urutan' => 1,
                'tgl_mulai' => now()->addDays(3)->format('Y-m-d H:i'),
                'tgl_selesai' => now()->addDays(5)->format('Y-m-d H:i'),
            ])->assertSessionHasNoErrors();

        // Tipe fase unik per periode.
        $this->actingAs($this->admin)
            ->post(route('ujian.fase.store', $this->periode), [
                'tipe' => 'pelaksanaan', 'urutan' => 2,
                'tgl_mulai' => now()->format('Y-m-d H:i'), 'tgl_selesai' => now()->addDay()->format('Y-m-d H:i'),
            ])->assertSessionHasErrors('tipe');

        $fase = FasePeriode::first();
        $this->assertNull($this->periode->fresh()->fase_aktif);

        $this->actingAs($this->admin)->post(route('ujian.fase.override', $fase), ['aksi' => 'buka'])->assertSessionHas('success');
        $this->assertSame('pelaksanaan', $this->periode->fresh()->fase_aktif);

        $this->actingAs($this->guru->user)->post(route('ujian.fase.override', $fase), ['aksi' => 'reset'])->assertForbidden();
    }

    public function test_periode_berisi_ujian_tidak_bisa_dihapus(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('ujian.periode.destroy', $this->periode))
            ->assertSessionHas('error');

        $this->assertModelExists($this->periode);
    }
}
