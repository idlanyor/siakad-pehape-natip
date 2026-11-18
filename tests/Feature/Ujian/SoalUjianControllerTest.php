<?php

namespace Tests\Feature\Ujian;

use App\Modules\Ujian\Models\BankSoal;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Ujian\Concerns\SiapkanSekolah;
use Tests\TestCase;

class SoalUjianControllerTest extends TestCase
{
    use RefreshDatabase, SiapkanSekolah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSekolah();
    }

    private function nomor(): array
    {
        return $this->ujian->soal()->pluck('pertanyaan', 'nomor')->all();
    }

    public function test_tulis_soal_mendapat_nomor_berikutnya(): void
    {
        foreach (['Soal satu', 'Soal dua'] as $teks) {
            $this->actingAs($this->guru->user)
                ->post(route('ujian.soal.store', $this->ujian), $this->dataSoalPg(['pertanyaan' => $teks]))
                ->assertSessionHasNoErrors();
        }

        $this->assertSame([1 => 'Soal satu', 2 => 'Soal dua'], $this->nomor());
    }

    public function test_ambil_dari_bank_hanya_mapel_sama_yang_terlihat_dan_tanpa_duplikat(): void
    {
        $bankSaya = BankSoal::factory()->create(['mapel_id' => $this->mapel->id, 'created_by' => $this->guru->user_id, 'pertanyaan' => 'Dari bank']);
        $bankMapelLain = BankSoal::factory()->create(['created_by' => $this->guru->user_id]);

        $this->actingAs($this->guru->user)
            ->get(route('ujian.soal.index', $this->ujian))
            ->assertInertia(fn (Assert $p) => $p->component('Ujian/Soal')->has('bank', 1)->where('bank.0.id', $bankSaya->id));

        $kirim = fn () => $this->actingAs($this->guru->user)
            ->post(route('ujian.soal.dari-bank', $this->ujian), ['bank_soal_ids' => [$bankSaya->id, $bankMapelLain->id]]);

        $kirim()->assertSessionHas('success', '1 soal ditambahkan dari bank soal.');
        $kirim()->assertSessionHas('success', '0 soal ditambahkan dari bank soal.');
        $this->assertSame([1 => 'Dari bank'], $this->nomor());
    }

    public function test_urutkan_dan_hapus_menomori_ulang(): void
    {
        [$a, $b, $c] = collect(['A', 'B', 'C'])->map(fn ($t, $i) => SoalUjian::factory()->create([
            'ujian_id' => $this->ujian->id, 'nomor' => $i + 1, 'pertanyaan' => $t,
        ]))->all();

        $this->actingAs($this->guru->user)
            ->put(route('ujian.soal.urutan', $this->ujian), ['soal_ids' => [$c->id, $a->id, $b->id]])
            ->assertSessionHas('success');
        $this->assertSame([1 => 'C', 2 => 'A', 3 => 'B'], $this->nomor());

        $this->actingAs($this->guru->user)
            ->put(route('ujian.soal.urutan', $this->ujian), ['soal_ids' => [$c->id, $a->id]])
            ->assertSessionHas('error');

        $this->actingAs($this->guru->user)->delete(route('ujian.soal.destroy', $a))->assertSessionHas('success');
        $this->assertSame([1 => 'C', 2 => 'B'], $this->nomor());
    }

    public function test_soal_terkunci_setelah_ada_sesi(): void
    {
        $soal = SoalUjian::factory()->create(['ujian_id' => $this->ujian->id, 'nomor' => 1]);
        SesiUjian::factory()->create(['ujian_id' => $this->ujian->id, 'siswa_id' => $this->siswa->id]);

        $this->actingAs($this->guru->user)
            ->post(route('ujian.soal.store', $this->ujian), $this->dataSoalPg())
            ->assertSessionHas('error', 'Soal tidak bisa diubah karena ujian sudah mulai dikerjakan siswa.');
        $this->actingAs($this->guru->user)
            ->put(route('ujian.soal.update', $soal), $this->dataSoalPg())
            ->assertSessionHas('error');
        $this->actingAs($this->guru->user)->delete(route('ujian.soal.destroy', $soal))->assertSessionHas('error');

        $this->assertSame(1, $this->ujian->soal()->count());
    }

    public function test_guru_lain_tidak_bisa_menyusun_soal(): void
    {
        $this->actingAs($this->guruLain->user)->get(route('ujian.soal.index', $this->ujian))->assertForbidden();
        $this->actingAs($this->guruLain->user)->post(route('ujian.soal.store', $this->ujian), $this->dataSoalPg())->assertForbidden();
    }
}
