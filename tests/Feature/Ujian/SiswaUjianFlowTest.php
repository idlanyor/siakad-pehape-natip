<?php

namespace Tests\Feature\Ujian;

use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\SesiUjianService;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Ujian\Concerns\SiapkanSekolah;
use Tests\TestCase;

class SiswaUjianFlowTest extends TestCase
{
    use RefreshDatabase, SiapkanSekolah;

    private SoalUjian $pg1;

    private SoalUjian $pg2;

    private SoalUjian $essay;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSekolah();

        $this->ujian->update([
            'status' => Ujian::STATUS_PUBLISHED,
            'waktu_mulai' => now()->subMinutes(10),
            'waktu_selesai' => now()->addHours(2),
            'durasi_menit' => 60,
        ]);
        $this->pg1 = SoalUjian::factory()->create(['ujian_id' => $this->ujian->id, 'nomor' => 1, 'kunci_jawaban' => 'A', 'bobot_nilai' => 1]);
        $this->pg2 = SoalUjian::factory()->create(['ujian_id' => $this->ujian->id, 'nomor' => 2, 'kunci_jawaban' => 'B', 'bobot_nilai' => 1]);
        $this->essay = SoalUjian::factory()->essay()->create(['ujian_id' => $this->ujian->id, 'nomor' => 3, 'bobot_nilai' => 2]);
    }

    private function mulai(): SesiUjian
    {
        $this->actingAs($this->siswa->user)->post(route('ujian.siswa.mulai', $this->ujian))->assertRedirect();

        return SesiUjian::where('siswa_id', $this->siswa->id)->sole();
    }

    public function test_alur_lengkap_mulai_menjawab_kumpulkan_hasil(): void
    {
        $user = $this->siswa->user;

        $this->actingAs($user)
            ->get(route('ujian.siswa.index'))
            ->assertInertia(fn (Assert $p) => $p->component('Siswa/Daftar')
                ->has('ujian', 1)
                ->where('ujian.0.jendela', 'dibuka')
                ->where('ujian.0.sesi', null));

        $this->actingAs($user)->post(route('ujian.siswa.mulai', $this->ujian))
            ->assertRedirect(route('ujian.siswa.kerjakan', $sesi = SesiUjian::sole()));

        $this->actingAs($user)
            ->get(route('ujian.siswa.kerjakan', $sesi))
            ->assertInertia(fn (Assert $p) => $p->component('Siswa/Kerjakan')
                ->has('soal', 3)
                ->missing('soal.0.kunci_jawaban')
                ->where('sesi.status', 'berlangsung'));

        $jawab = fn (array $data) => $this->actingAs($user)->postJson(route('ujian.siswa.jawab', $sesi), $data);
        $jawab(['soal_id' => $this->pg1->id, 'pilihan' => 'a'])->assertOk()->assertJson(['status' => 'berlangsung']);
        $jawab(['soal_id' => $this->pg2->id, 'pilihan' => 'C', 'ragu' => true])->assertOk();
        $jawab(['soal_id' => $this->essay->id, 'teks' => 'Uraian saya'])->assertOk();

        $this->actingAs($user)->post(route('ujian.siswa.selesai', $sesi))
            ->assertRedirect(route('ujian.siswa.hasil', $sesi));

        $this->actingAs($user)
            ->get(route('ujian.siswa.hasil', $sesi))
            ->assertInertia(fn (Assert $p) => $p->component('Siswa/Hasil')
                ->where('sesi.status', 'selesai')
                ->where('nilai.akhir', 25)
                ->where('nilai.menunggu_koreksi', true)
                ->where('progres.terjawab', 3));

        // Setelah selesai, halaman mengerjakan mengalihkan ke hasil dan jawaban ditolak.
        $this->actingAs($user)->get(route('ujian.siswa.kerjakan', $sesi))->assertRedirect(route('ujian.siswa.hasil', $sesi));
        $jawab(['soal_id' => $this->pg2->id, 'pilihan' => 'B'])->assertStatus(422);
    }

    public function test_daftar_hanya_ujian_terbit_untuk_kelasnya(): void
    {
        Ujian::factory()->create(['kelas_id' => $this->kelas->id]); // draft
        Ujian::factory()->published()->create(); // kelas lain
        Ujian::factory()->published()->create([
            'kelas_id' => $this->kelas->id,
            'waktu_mulai' => now()->addDay(),
            'waktu_selesai' => now()->addDay()->addHours(2),
        ]);

        $this->actingAs($this->siswa->user)
            ->get(route('ujian.siswa.index'))
            ->assertInertia(fn (Assert $p) => $p->has('ujian', 2)
                ->where('ujian.0.jendela', 'belum_dibuka')
                ->where('ujian.1.jendela', 'dibuka'));
    }

    public function test_tidak_bisa_mulai_ujian_kelas_lain_atau_draft(): void
    {
        $siswaLain = DataSekolah::siswa();
        $this->actingAs($siswaLain->user)->post(route('ujian.siswa.mulai', $this->ujian))->assertForbidden();

        $this->ujian->update(['status' => Ujian::STATUS_DRAFT]);
        $this->actingAs($this->siswa->user)->post(route('ujian.siswa.mulai', $this->ujian))->assertForbidden();
    }

    public function test_mulai_di_luar_jadwal_memberi_pesan(): void
    {
        $this->ujian->update(['waktu_mulai' => now()->addHour(), 'waktu_selesai' => now()->addHours(3)]);

        $this->actingAs($this->siswa->user)
            ->from(route('ujian.siswa.index'))
            ->post(route('ujian.siswa.mulai', $this->ujian))
            ->assertRedirect(route('ujian.siswa.index'))
            ->assertSessionHas('error', 'Ujian tidak sedang dalam waktu pelaksanaan.');
    }

    public function test_siswa_lain_dan_guru_tidak_bisa_mengakses_sesi(): void
    {
        $sesi = $this->mulai();
        $siswaSekelas = DataSekolah::siswa($this->kelas);

        $this->actingAs($siswaSekelas->user)->get(route('ujian.siswa.kerjakan', $sesi))->assertForbidden();
        $this->actingAs($siswaSekelas->user)->postJson(route('ujian.siswa.jawab', $sesi), ['soal_id' => $this->pg1->id, 'pilihan' => 'A'])->assertForbidden();
        $this->actingAs($siswaSekelas->user)->get(route('ujian.siswa.hasil', $sesi))->assertForbidden();
        $this->actingAs($this->guru->user)->get(route('ujian.siswa.kerjakan', $sesi))->assertForbidden();
        $this->actingAs($this->guru->user)->get(route('ujian.siswa.index'))->assertForbidden();
    }

    public function test_jawaban_untuk_soal_ujian_lain_ditolak(): void
    {
        $sesi = $this->mulai();
        $soalLain = SoalUjian::factory()->create();

        $this->actingAs($this->siswa->user)
            ->postJson(route('ujian.siswa.jawab', $sesi), ['soal_id' => $soalLain->id, 'pilihan' => 'A'])
            ->assertNotFound();

        $this->actingAs($this->siswa->user)
            ->postJson(route('ujian.siswa.jawab', $sesi), ['soal_id' => $this->pg1->id, 'pilihan' => 'E'])
            ->assertStatus(422)
            ->assertJson(['message' => 'Pilihan jawaban tidak valid.']);
    }

    public function test_waktu_habis_saat_menjawab(): void
    {
        $sesi = $this->mulai();
        $this->travel(61)->minutes();

        $this->actingAs($this->siswa->user)
            ->postJson(route('ujian.siswa.jawab', $sesi), ['soal_id' => $this->pg1->id, 'pilihan' => 'A'])
            ->assertStatus(422)
            ->assertJson(['message' => 'Waktu ujian telah habis.']);

        $this->actingAs($this->siswa->user)
            ->postJson(route('ujian.siswa.ping', $sesi))
            ->assertOk()
            ->assertJson(['status' => 'timeout', 'sisa_detik' => 0]);
    }

    public function test_pelanggaran_sampai_diskualifikasi(): void
    {
        config(['ujian.batas_pelanggaran' => 2]);
        $sesi = $this->mulai();
        $lapor = fn (string $tipe) => $this->actingAs($this->siswa->user)->postJson(route('ujian.siswa.pelanggaran', $sesi), ['tipe' => $tipe]);

        $lapor('bukan_tipe')->assertUnprocessable();
        $lapor('tab_switch')->assertOk()->assertJson(['status' => 'berlangsung', 'jumlah_pelanggaran' => 1]);
        $lapor('copy')->assertOk()->assertJson(['status' => 'diskualifikasi', 'jumlah_pelanggaran' => 2]);

        $this->actingAs($this->siswa->user)
            ->get(route('ujian.siswa.hasil', $sesi))
            ->assertInertia(fn (Assert $p) => $p->where('sesi.status', 'diskualifikasi'));
    }

    public function test_ping_memperbarui_aktivitas_dan_mendeteksi_paksa_selesai(): void
    {
        $sesi = $this->mulai();
        $this->travel(5)->minutes();

        $this->actingAs($this->siswa->user)->postJson(route('ujian.siswa.ping', $sesi))
            ->assertOk()
            ->assertJson(['status' => 'berlangsung']);
        $this->assertTrue($sesi->fresh()->last_activity_at->isAfter(now()->subSeconds(5)));

        app(SesiUjianService::class)->paksaSelesai($sesi, $this->guru->user);

        $this->actingAs($this->siswa->user)->postJson(route('ujian.siswa.ping', $sesi))
            ->assertJson(['status' => 'selesai', 'sisa_detik' => 0]);
    }

    public function test_hasil_sebelum_selesai_dialihkan_ke_halaman_mengerjakan(): void
    {
        $sesi = $this->mulai();

        $this->actingAs($this->siswa->user)
            ->get(route('ujian.siswa.hasil', $sesi))
            ->assertRedirect(route('ujian.siswa.kerjakan', $sesi));
    }

    public function test_nilai_disembunyikan_bila_tampilkan_nilai_mati(): void
    {
        $this->ujian->update(['tampilkan_nilai' => false]);
        $sesi = $this->mulai();
        $this->actingAs($this->siswa->user)->post(route('ujian.siswa.selesai', $sesi));

        $this->actingAs($this->siswa->user)
            ->get(route('ujian.siswa.hasil', $sesi))
            ->assertInertia(fn (Assert $p) => $p->where('tampilkan_nilai', false)->where('nilai', null));
    }

    public function test_endpoint_json_dibatasi_rate_limit(): void
    {
        $sesi = $this->mulai();

        for ($i = 0; $i < 120; $i++) {
            $this->actingAs($this->siswa->user)->postJson(route('ujian.siswa.ping', $sesi))->assertOk();
        }

        $this->actingAs($this->siswa->user)->postJson(route('ujian.siswa.ping', $sesi))->assertStatus(429);
    }
}
