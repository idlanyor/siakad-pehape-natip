<?php

namespace Tests\Feature\Ujian;

use App\Models\User;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use App\Modules\Ujian\Services\PenyajiSesi;
use App\Modules\Ujian\Services\ProktorService;
use App\Modules\Ujian\Services\SesiUjianService;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenyajiDanProktorTest extends TestCase
{
    use RefreshDatabase;

    private SesiUjianService $sesiService;

    private Ujian $ujian;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sesiService = app(SesiUjianService::class);

        $this->ujian = Ujian::factory()->published()->create(['durasi_menit' => 30]);
        SoalUjian::factory()->create(['ujian_id' => $this->ujian->id, 'nomor' => 1, 'kunci_jawaban' => 'C']);
        SoalUjian::factory()->essay()->create(['ujian_id' => $this->ujian->id, 'nomor' => 2]);
    }

    private function mulaiSesi(): SesiUjian
    {
        return $this->sesiService->mulai($this->ujian, DataSekolah::siswa($this->ujian->kelas));
    }

    public function test_pengerjaan_tidak_membocorkan_kunci_atau_nilai(): void
    {
        $sesi = $this->mulaiSesi();
        $this->sesiService->simpanJawaban($sesi, $this->ujian->soal->first(), 'C', null);

        $data = app(PenyajiSesi::class)->pengerjaan($sesi->fresh());
        $json = json_encode($data);

        $this->assertStringNotContainsString('kunci_jawaban', $json);
        $this->assertStringNotContainsString('is_benar', $json);
        $this->assertStringNotContainsString('"nilai', $json);
        $this->assertSame(['total' => 2, 'terjawab' => 1, 'ragu' => 0], $data['progres']);
        $this->assertEqualsWithDelta(30 * 60, $data['sesi']['sisa_detik'], 2);
    }

    public function test_opsi_teracak_tetap_mengirim_kode_asli(): void
    {
        $this->ujian->update(['acak_opsi' => true]);
        $sesi = $this->mulaiSesi();
        $sesi->jawaban()->whereNotNull('urutan_opsi')->update(['urutan_opsi' => 'DBAC']);

        $opsi = app(PenyajiSesi::class)->pengerjaan($sesi->fresh())['soal'][0]['opsi'];

        $this->assertSame(['D', 'B', 'A', 'C'], array_column($opsi, 'kode'));
        $this->assertSame(['A', 'B', 'C', 'D'], array_column($opsi, 'label'));
        $this->assertSame('Opsi D', $opsi[0]['html']);
    }

    public function test_hasil_menyembunyikan_nilai_bila_tampilkan_nilai_mati(): void
    {
        $sesi = $this->mulaiSesi();
        $this->sesiService->selesaikan($sesi);
        $penyaji = app(PenyajiSesi::class);

        $hasil = $penyaji->hasil($sesi->fresh());
        $this->assertTrue($hasil['nilai']['menunggu_koreksi']);
        $this->assertFalse($hasil['nilai']['lulus']);

        $this->ujian->update(['tampilkan_nilai' => false]);
        $this->assertNull($penyaji->hasil($sesi->fresh()->load('ujian'))['nilai']);
    }

    public function test_peserta_mencakup_siswa_yang_belum_mulai(): void
    {
        $sesi = $this->mulaiSesi();
        $this->sesiService->simpanJawaban($sesi, $this->ujian->soal->first(), 'A', null);
        $this->sesiService->simpanJawaban($sesi, $this->ujian->soal->last(), null, '');
        $belum = DataSekolah::siswa($this->ujian->kelas);

        $peserta = app(ProktorService::class)->peserta($this->ujian)->keyBy('siswa.id');

        $this->assertCount(2, $peserta);
        $this->assertNull($peserta[$belum->id]['sesi']);
        $ringkas = $peserta[$sesi->siswa_id]['sesi'];
        $this->assertSame(1, $ringkas['terjawab'], 'Essay kosong tidak dihitung terjawab.');
        $this->assertSame(2, $ringkas['total_soal']);
        $this->assertSame('online', $ringkas['koneksi']);
    }

    public function test_statistik_ujian(): void
    {
        $selesai = $this->mulaiSesi();
        $this->sesiService->selesaikan($selesai);
        $this->sesiService->catatPelanggaran($this->mulaiSesi(), 'tab_switch');
        DataSekolah::siswa($this->ujian->kelas);

        $this->assertSame([
            'peserta' => 3,
            'belum_mulai' => 1,
            'berlangsung' => 1,
            'selesai' => 1,
            'diskualifikasi' => 0,
            'total_pelanggaran' => 1,
        ], app(ProktorService::class)->statistik($this->ujian));
    }

    public function test_status_koneksi_berdasarkan_aktivitas_terakhir(): void
    {
        $sesi = $this->mulaiSesi();
        $proktor = app(ProktorService::class);

        $this->assertSame('online', $proktor->statusKoneksi($sesi));
        $this->travel(3)->minutes();
        $this->assertSame('idle', $proktor->statusKoneksi($sesi));
        $this->travel(5)->minutes();
        $this->assertSame('offline', $proktor->statusKoneksi($sesi));
    }

    public function test_event_terbaru_hanya_untuk_ujian_ini(): void
    {
        $sesi = $this->mulaiSesi();
        $this->sesiService->catatPelanggaran($sesi, 'copy');
        $this->sesiService->paksaSelesai($sesi, User::factory()->create(['name' => 'Bu Proktor']));

        $ujianLain = SesiUjian::factory()->create();
        $this->sesiService->catatPelanggaran($ujianLain, 'paste');

        $events = app(ProktorService::class)->eventTerbaru($this->ujian);

        $this->assertSame(['force_finish', 'copy'], $events->pluck('tipe')->all());
        $this->assertSame('Bu Proktor', $events->first()['oleh']);
        $this->assertSame($sesi->siswa->nama, $events->last()['siswa']);
    }
}
