<?php

namespace Tests\Feature\Ujian;

use App\Models\Notifikasi;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\PengelolaanUjianService;
use App\Modules\Ujian\Services\PenilaianService;
use App\Modules\Ujian\Services\SesiUjianService;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\Ujian\Concerns\SiapkanSekolah;
use Tests\TestCase;

/**
 * Titik temu modul ujian dengan bagian SIAKAD lain, setelah microservice dibongkar.
 */
class IntegrasiSiakadTest extends TestCase
{
    use RefreshDatabase, SiapkanSekolah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanSekolah();
        $this->ujian->update(['waktu_mulai' => now()->subMinutes(5), 'waktu_selesai' => now()->addHour()]);
    }

    public function test_endpoint_integrasi_microservice_sudah_tidak_ada(): void
    {
        // Sebelumnya terbuka tanpa autentikasi: bisa dipakai menebak password siswa.
        $this->postJson('/api/ujian-service/verify-credentials', ['nis' => $this->siswa->nisn, 'password' => 'x'])->assertNotFound();
        $this->postJson('/api/ujian-service/sync/siswa')->assertNotFound();
        $this->getJson('/api/ujian-service/health')->assertNotFound();
        $this->postJson('/api/jwt/generate')->assertNotFound();
    }

    public function test_login_nis_mengarah_ke_daftar_ujian(): void
    {
        $this->siswa->user->update(['password' => Hash::make('rahasia123')]);

        $this->get(route('ujian.login'))->assertOk()->assertSee(route('ujian.login.store'), false);

        $this->post(route('ujian.login.store'), ['nis' => $this->siswa->nisn, 'password' => 'rahasia123'])
            ->assertRedirect(route('ujian.siswa.index'));
        $this->assertAuthenticatedAs($this->siswa->user);
    }

    /** Regresi: "/" sempat diubah menjadi login NIS; seharusnya landing page sekolah. */
    public function test_halaman_depan_adalah_landing_dan_login_tertaut(): void
    {
        $this->get('/')->assertOk()->assertViewIs('public.landing.index');
        $this->get('/landing')->assertRedirect('/');
        $this->get(route('login'))->assertSee(route('ujian.login'), false);
        $this->actingAs($this->siswa->user)->get(route('ujian.login'))->assertRedirect();
    }

    public function test_modul_berada_di_prefix_ujian(): void
    {
        $this->assertSame(url('/ujian/saya'), route('ujian.siswa.index'));
        $this->assertSame(url('/ujian/proktor'), route('ujian.proktor.index'));
    }

    public function test_widget_dashboard_siswa_menghitung_ujian_yang_bisa_dikerjakan(): void
    {
        $this->ujian->update(['status' => Ujian::STATUS_PUBLISHED]);
        SoalUjian::factory()->create(['ujian_id' => $this->ujian->id, 'nomor' => 1]);
        Ujian::factory()->published()->create(['kelas_id' => $this->kelas->id, 'waktu_mulai' => now()->addDay(), 'waktu_selesai' => now()->addDays(2)]);

        $this->actingAs($this->siswa->user)->get(route('siswa.dashboard'))
            ->assertSee('<strong>1 ujian</strong>', false)
            ->assertSee(route('ujian.siswa.index'), false);

        $service = app(SesiUjianService::class);
        $service->selesaikan($service->mulai($this->ujian, $this->siswa));

        $this->actingAs($this->siswa->user)->get(route('siswa.dashboard'))->assertDontSee('Ujian Tersedia!');
    }

    public function test_menerbitkan_ujian_memberi_notifikasi_siswa_kelas(): void
    {
        SoalUjian::factory()->create(['ujian_id' => $this->ujian->id, 'nomor' => 1]);
        $siswaKelasLain = DataSekolah::siswa();

        app(PengelolaanUjianService::class)->publikasikan($this->ujian);

        $notif = Notifikasi::where('user_id', $this->siswa->user_id)->sole();
        $this->assertSame('Ujian baru: '.$this->ujian->judul, $notif->judul);
        $this->assertSame(route('ujian.siswa.index'), $notif->url);
        $this->assertFalse(Notifikasi::where('user_id', $siswaKelasLain->user_id)->exists());
    }

    public function test_notifikasi_dikoreksi_sekali_saat_essay_terakhir_dinilai(): void
    {
        $this->ujian->update(['status' => Ujian::STATUS_PUBLISHED]);
        foreach ([1, 2] as $nomor) {
            SoalUjian::factory()->essay()->create(['ujian_id' => $this->ujian->id, 'nomor' => $nomor, 'bobot_nilai' => 5]);
        }
        $service = app(SesiUjianService::class);
        $sesi = $service->selesaikan($service->mulai($this->ujian, $this->siswa));
        [$e1, $e2] = $sesi->jawaban->all();
        $penilaian = app(PenilaianService::class);
        $jumlah = fn () => Notifikasi::where('user_id', $this->siswa->user_id)->count();

        $penilaian->nilaiEssay($e1, 5);
        $this->assertSame(0, $jumlah(), 'Masih ada essay yang belum dinilai.');

        $penilaian->nilaiEssay($e2, 3);
        $this->assertSame(1, $jumlah());
        $this->assertStringContainsString('80.00', Notifikasi::where('user_id', $this->siswa->user_id)->sole()->pesan);

        $penilaian->nilaiEssay($e2->fresh(), 4);
        $this->assertSame(1, $jumlah(), 'Mengubah nilai tidak mengirim ulang notifikasi.');
    }
}
