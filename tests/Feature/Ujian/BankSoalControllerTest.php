<?php

namespace Tests\Feature\Ujian;

use App\Modules\Ujian\Models\BankSoal;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Ujian\Concerns\SiapkanSekolah;
use Tests\TestCase;

class BankSoalControllerTest extends TestCase
{
    use RefreshDatabase, SiapkanSekolah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSekolah();
    }

    private function data(array $timpa = []): array
    {
        return [...$this->dataSoalPg(), 'mapel_id' => $this->mapel->id, 'tingkat_kesulitan' => 'mudah', 'tag' => ['html'], ...$timpa];
    }

    public function test_guru_menambah_soal_untuk_mapel_yang_diampu(): void
    {
        $this->actingAs($this->guru->user)
            ->post(route('ujian.bank-soal.store'), $this->data())
            ->assertRedirect(route('ujian.bank-soal.index'));

        $soal = BankSoal::sole();
        $this->assertSame('A', $soal->kunci_jawaban);
        $this->assertSame($this->guru->user_id, $soal->created_by);
        $this->assertSame(['html'], $soal->tag);
        $this->assertSame('2.00', $soal->bobot_default);
    }

    public function test_guru_tidak_bisa_memakai_mapel_lain(): void
    {
        $this->actingAs($this->guru->user)
            ->post(route('ujian.bank-soal.store'), $this->data(['mapel_id' => DataSekolah::mapel()->id]))
            ->assertSessionHasErrors('mapel_id');
    }

    public function test_kunci_harus_menunjuk_opsi_terisi(): void
    {
        $this->actingAs($this->guru->user)
            ->post(route('ujian.bank-soal.store'), $this->data(['kunci_jawaban' => 'C']))
            ->assertSessionHasErrors('kunci_jawaban');
    }

    public function test_essay_mengosongkan_opsi_dan_kunci(): void
    {
        $this->actingAs($this->guru->user)
            ->post(route('ujian.bank-soal.store'), $this->data(['tipe' => 'essay']))
            ->assertSessionHasNoErrors();

        $soal = BankSoal::sole();
        $this->assertNull($soal->opsi_a);
        $this->assertNull($soal->kunci_jawaban);
    }

    public function test_daftar_hanya_soal_yang_boleh_dilihat(): void
    {
        BankSoal::factory()->create(['mapel_id' => $this->mapel->id, 'created_by' => $this->guruLain->user_id]);
        BankSoal::factory()->create(); // mapel lain, pembuat lain

        $this->actingAs($this->guru->user)
            ->get(route('ujian.bank-soal.index'))
            ->assertInertia(fn (Assert $p) => $p
                ->component('BankSoal/Index')
                ->has('soal.data', 1)
                ->where('soal.data.0.bisa_ubah', false));

        $this->actingAs($this->admin)
            ->get(route('ujian.bank-soal.index'))
            ->assertInertia(fn (Assert $p) => $p->has('soal.data', 2));
    }

    public function test_hanya_pembuat_yang_bisa_mengubah(): void
    {
        $soal = BankSoal::factory()->create(['mapel_id' => $this->mapel->id, 'created_by' => $this->guruLain->user_id]);

        $this->actingAs($this->guru->user)->get(route('ujian.bank-soal.edit', $soal))->assertForbidden();
        $this->actingAs($this->guru->user)->delete(route('ujian.bank-soal.destroy', $soal))->assertForbidden();
        $this->actingAs($this->siswa->user)->get(route('ujian.bank-soal.index'))->assertForbidden();
    }
}
