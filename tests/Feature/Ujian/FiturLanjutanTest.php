<?php

namespace Tests\Feature\Ujian;

use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\SesiUjianService;
use App\Modules\Ujian\Support\TeksSoal;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Ujian\Concerns\SiapkanSekolah;
use Tests\TestCase;

/**
 * Pengumuman & tambah waktu proktor, satu perangkat per sesi, dan teks soal
 * berformat + gambar.
 */
class FiturLanjutanTest extends TestCase
{
    use RefreshDatabase, SiapkanSekolah;

    private SoalUjian $soal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSekolah();
        $this->ujian->update([
            'status' => Ujian::STATUS_PUBLISHED,
            'waktu_mulai' => now()->subMinutes(10),
            'waktu_selesai' => now()->addMinutes(50),
            'durasi_menit' => 30,
        ]);
        $this->soal = SoalUjian::factory()->create(['ujian_id' => $this->ujian->id, 'nomor' => 1, 'kunci_jawaban' => 'A']);
    }

    /** Buka halaman mengerjakan dan kembalikan token perangkatnya. */
    private function bukaHalaman(SesiUjian $sesi): string
    {
        $token = null;
        $this->actingAs($this->siswa->user)
            ->get(route('ujian.siswa.kerjakan', $sesi))
            ->assertInertia(function (Assert $p) use (&$token) {
                $token = $p->toArray()['props']['tokenPerangkat'];
            });

        return $token;
    }

    private function mulai(): SesiUjian
    {
        return app(SesiUjianService::class)->mulai($this->ujian, $this->siswa);
    }

    // --- Satu perangkat per sesi ---------------------------------------------------

    public function test_perangkat_lama_ditolak_setelah_sesi_dibuka_di_perangkat_lain(): void
    {
        $sesi = $this->mulai();
        $tokenLaptop = $this->bukaHalaman($sesi);
        $tokenHp = $this->bukaHalaman($sesi);
        $this->assertNotSame($tokenLaptop, $tokenHp);

        $jawab = fn (string $token) => $this->actingAs($this->siswa->user)
            ->postJson(route('ujian.siswa.jawab', $sesi), ['soal_id' => $this->soal->id, 'pilihan' => 'A'], ['X-Sesi-Token' => $token]);

        $jawab($tokenLaptop)->assertStatus(409)->assertJson(['kode' => 'perangkat_lain']);
        $jawab($tokenHp)->assertOk();
        $this->actingAs($this->siswa->user)->postJson(route('ujian.siswa.ping', $sesi), [], ['X-Sesi-Token' => 'salah'])->assertStatus(409);

        // Perpindahan saat perangkat lama masih aktif terlihat oleh proktor.
        $event = $sesi->proktorEvents()->tipe('device_switch')->sole();
        $this->assertArrayHasKey('ip', $event->detail);
        $this->assertSame(0, $sesi->fresh()->jumlah_pelanggaran, 'Pindah perangkat tidak dihitung pelanggaran.');
    }

    public function test_kumpulkan_dari_perangkat_lama_tidak_mengambil_alih_lagi(): void
    {
        $sesi = $this->mulai();
        $tokenLama = $this->bukaHalaman($sesi);
        $tokenBaru = $this->bukaHalaman($sesi);

        $this->actingAs($this->siswa->user)
            ->post(route('ujian.siswa.selesai', $sesi), [], ['X-Sesi-Token' => $tokenLama])
            ->assertRedirect(route('ujian.siswa.index'))
            ->assertSessionHas('error');

        $this->assertTrue($sesi->fresh()->isBerlangsung());
        $this->assertSame($tokenBaru, $sesi->fresh()->token_perangkat);
    }

    public function test_token_perangkat_tidak_bocor_ke_props_lain(): void
    {
        $sesi = $this->mulai();
        $this->bukaHalaman($sesi);

        $this->assertArrayNotHasKey('token_perangkat', $sesi->fresh()->toArray());
    }

    public function test_reset_menghapus_token_perangkat(): void
    {
        $sesi = $this->mulai();
        $this->bukaHalaman($sesi);

        app(SesiUjianService::class)->reset($sesi, $this->guru->user);

        $this->assertNull($sesi->fresh()->token_perangkat);
    }

    // --- Tambah waktu -------------------------------------------------------------------

    public function test_tambah_waktu_satu_siswa_melewati_jam_tutup(): void
    {
        $this->ujian->update(['waktu_selesai' => now()->addMinutes(5)]);
        $sesi = $this->mulai();
        $service = app(SesiUjianService::class);
        $this->assertEqualsWithDelta(5 * 60, $service->sisaDetik($sesi), 2);

        $this->actingAs($this->guru->user)
            ->post(route('ujian.proktor.tambah-waktu', $sesi), ['menit' => 10, 'alasan' => 'Komputer mati'])
            ->assertSessionHas('success');

        $sesi->refresh();
        $this->assertSame(600, $sesi->tambahan_detik);
        $this->assertEqualsWithDelta(15 * 60, $service->sisaDetik($sesi), 2, 'Jam tutup ikut mundur 10 menit untuk sesi ini.');
        $this->assertSame('+10 menit: Komputer mati', $sesi->proktorEvents()->tipe('extend_time')->sole()->keterangan);

        $this->travel(7)->minutes();
        $this->assertFalse($service->periksaWaktu($sesi), 'Belum habis karena ada tambahan.');
    }

    public function test_tambah_waktu_semua_peserta_dan_otomatis_diumumkan(): void
    {
        $berlangsung = $this->mulai();
        $sudahSelesai = app(SesiUjianService::class)->selesaikan(
            app(SesiUjianService::class)->mulai($this->ujian, DataSekolah::siswa($this->kelas))
        );

        $this->actingAs($this->guru->user)
            ->post(route('ujian.proktor.tambah-waktu-semua', $this->ujian), ['menit' => 5])
            ->assertSessionHas('success', 'Waktu 1 peserta yang sedang mengerjakan ditambah 5 menit.');

        $this->assertSame(300, $berlangsung->fresh()->tambahan_detik);
        $this->assertSame(0, $sudahSelesai->fresh()->tambahan_detik);
        $this->assertSame('Waktu ujian ditambah 5 menit.', $this->ujian->pengumuman()->sole()->pesan);
    }

    public function test_validasi_dan_akses_tambah_waktu(): void
    {
        $sesi = $this->mulai();

        $this->actingAs($this->guru->user)->post(route('ujian.proktor.tambah-waktu', $sesi), ['menit' => 0])->assertSessionHasErrors('menit');
        $this->actingAs($this->guru->user)->post(route('ujian.proktor.tambah-waktu', $sesi), ['menit' => 181])->assertSessionHasErrors('menit');
        $this->actingAs($this->guruLain->user)->post(route('ujian.proktor.tambah-waktu', $sesi), ['menit' => 5])->assertForbidden();
    }

    // --- Pengumuman ------------------------------------------------------------------------

    public function test_pengumuman_sampai_ke_siswa_lewat_ping(): void
    {
        $sesi = $this->mulai();
        $token = $this->bukaHalaman($sesi);

        $this->actingAs($this->guru->user)
            ->post(route('ujian.proktor.pengumuman', $this->ujian), ['pesan' => 'Soal 1 opsi B diralat.'])
            ->assertSessionHas('success');

        $ping = fn (int $setelah) => $this->actingAs($this->siswa->user)
            ->postJson(route('ujian.siswa.ping', $sesi), ['setelah' => $setelah], ['X-Sesi-Token' => $token]);

        $respons = $ping(0)->assertOk()->assertJsonPath('pengumuman.0.pesan', 'Soal 1 opsi B diralat.');
        $id = $respons->json('pengumuman.0.id');
        $ping($id)->assertJsonCount(0, 'pengumuman');

        $this->actingAs($this->guru->user)->get(route('ujian.proktor.monitor', $this->ujian))
            ->assertInertia(fn (Assert $p) => $p->where('pengumuman.0.oleh', $this->guru->user->name));
    }

    public function test_hanya_pengawas_yang_bisa_mengumumkan(): void
    {
        $this->actingAs($this->guruLain->user)
            ->post(route('ujian.proktor.pengumuman', $this->ujian), ['pesan' => 'x'])
            ->assertForbidden();
        $this->actingAs($this->guru->user)
            ->post(route('ujian.proktor.pengumuman', $this->ujian), ['pesan' => ''])
            ->assertSessionHasErrors('pesan');
    }

    // --- Teks soal berformat & gambar ----------------------------------------------------------

    public function test_teks_soal_dirender_aman(): void
    {
        $html = TeksSoal::html("**Tebal** <script>alert(1)</script> [x](javascript:alert(1))\n\n"
            .'![ok](/storage/ujian/soal/a.png) ![luar](https://pelacak.example/p.png) ![naik](/storage/ujian/soal/../../x.png)');

        $this->assertStringContainsString('<strong>Tebal</strong>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('src="/storage/ujian/soal/a.png"', $html);
        $this->assertStringNotContainsString('pelacak.example', $html);
        $this->assertStringNotContainsString('../', $html);
    }

    public function test_siswa_menerima_soal_dalam_html_tersaring(): void
    {
        $this->soal->update(['pertanyaan' => 'Hitung **x** <img src=x onerror=alert(1)>', 'opsi_a' => '*satu*']);
        $sesi = $this->mulai();

        $this->actingAs($this->siswa->user)->get(route('ujian.siswa.kerjakan', $sesi))
            ->assertInertia(fn (Assert $p) => $p
                ->where('soal.0.pertanyaan_html', '<p>Hitung <strong>x</strong> &lt;img src=x onerror=alert(1)&gt;</p>')
                ->where('soal.0.opsi.0.html', '<em>satu</em>')
                ->missing('soal.0.pertanyaan'));
    }

    public function test_guru_mengunggah_gambar_soal(): void
    {
        Storage::fake('public');

        $respons = $this->actingAs($this->guru->user)
            ->post(route('ujian.media.store'), ['gambar' => UploadedFile::fake()->image('grafik.png', 400, 300)], ['Accept' => 'application/json'])
            ->assertCreated();

        $url = $respons->json('url');
        $this->assertStringStartsWith('/storage/ujian/soal/', $url);
        $this->assertSame("![gambar]({$url})", $respons->json('markdown'));
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $url));
        $this->assertTrue(TeksSoal::urlDiizinkan($url));
    }

    public function test_unggahan_selain_gambar_dan_oleh_siswa_ditolak(): void
    {
        Storage::fake('public');

        $this->actingAs($this->guru->user)
            ->postJson(route('ujian.media.store'), ['gambar' => UploadedFile::fake()->create('skrip.php', 10, 'application/x-php')])
            ->assertUnprocessable();
        $this->actingAs($this->guru->user)
            ->postJson(route('ujian.media.store'), ['gambar' => UploadedFile::fake()->image('besar.png')->size(3000)])
            ->assertUnprocessable();
        $this->actingAs($this->siswa->user)
            ->postJson(route('ujian.media.store'), ['gambar' => UploadedFile::fake()->image('a.png')])
            ->assertForbidden();
    }
}
