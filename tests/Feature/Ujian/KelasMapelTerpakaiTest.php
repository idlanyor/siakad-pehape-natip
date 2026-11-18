<?php

namespace Tests\Feature\Ujian;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Ujian\Concerns\SiapkanSekolah;
use Tests\TestCase;

class KelasMapelTerpakaiTest extends TestCase
{
    use RefreshDatabase, SiapkanSekolah;

    public function test_kelas_dan_mapel_yang_dipakai_ujian_tidak_bisa_dihapus(): void
    {
        $this->siapkanSekolah();

        $this->actingAs($this->admin)
            ->delete(route('admin.kelas.destroy', $this->kelas))
            ->assertSessionHas('error');
        $this->actingAs($this->admin)
            ->delete(route('admin.mapel.destroy', $this->mapel))
            ->assertSessionHas('error');

        $this->assertModelExists($this->kelas);
        $this->assertModelExists($this->mapel);
    }
}
