<?php

namespace Tests\Feature\Ujian;

use App\Models\User;
use App\Modules\Ujian\Models\BankSoal;
use App\Modules\Ujian\Models\FasePeriode;
use App\Modules\Ujian\Models\JawabanSiswa;
use App\Modules\Ujian\Models\PeriodeUjian;
use App\Modules\Ujian\Models\ProktorEvent;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_sesi_terhubung_langsung_ke_siswa_dan_kelas_siakad(): void
    {
        $sesi = SesiUjian::factory()->create();

        $this->assertSame($sesi->ujian->kelas_id, $sesi->siswa->kelas_id);
        $this->assertSame($sesi->siswa->kelas_id, $sesi->kelas_id);
        $this->assertTrue($sesi->siswa->sesiUjian->contains($sesi));
    }

    public function test_ujian_terhubung_ke_mapel_kelas_dan_periode(): void
    {
        $ujian = Ujian::factory()->create();

        $this->assertNotNull($ujian->mapel->nama_mapel);
        $this->assertNotNull($ujian->kelas->nama_kelas);
        $this->assertNotNull($ujian->periode->semester->tahunAjaran);
    }

    public function test_soal_ujian_diurutkan_berdasarkan_nomor(): void
    {
        $ujian = Ujian::factory()->create();
        SoalUjian::factory()->create(['ujian_id' => $ujian->id, 'nomor' => 3]);
        SoalUjian::factory()->create(['ujian_id' => $ujian->id, 'nomor' => 1]);
        SoalUjian::factory()->create(['ujian_id' => $ujian->id, 'nomor' => 2]);

        $this->assertSame([1, 2, 3], $ujian->soal->pluck('nomor')->all());
    }

    public function test_satu_sesi_per_siswa_per_ujian(): void
    {
        $sesi = SesiUjian::factory()->create();

        $this->expectException(QueryException::class);
        SesiUjian::factory()->create(['ujian_id' => $sesi->ujian_id, 'siswa_id' => $sesi->siswa_id]);
    }

    public function test_menghapus_siswa_ikut_menghapus_sesi_dan_jawabannya(): void
    {
        $jawaban = JawabanSiswa::factory()->create();
        $sesi = $jawaban->sesi;

        $sesi->siswa->delete();

        $this->assertModelMissing($sesi);
        $this->assertModelMissing($jawaban);
    }

    public function test_periode_yang_masih_punya_ujian_tidak_bisa_dihapus(): void
    {
        $ujian = Ujian::factory()->create();

        $this->expectException(QueryException::class);
        $ujian->periode->delete();
    }

    public function test_opsi_hanya_mengembalikan_opsi_yang_terisi(): void
    {
        $soal = BankSoal::factory()->create(['opsi_e' => null]);

        $this->assertSame(['A', 'B', 'C', 'D'], array_keys($soal->opsi()));
        $this->assertTrue($soal->isPilihanGanda());
        $this->assertSame([], BankSoal::factory()->essay()->create()->opsi());
    }

    public function test_salin_soal_dari_bank_soal(): void
    {
        $bank = BankSoal::factory()->create(['kunci_jawaban' => 'C', 'bobot_default' => 2.5]);
        $ujian = Ujian::factory()->create();

        $soal = $ujian->soal()->create(['nomor' => 1, ...SoalUjian::atributDariBankSoal($bank)]);

        $this->assertSame($bank->pertanyaan, $soal->pertanyaan);
        $this->assertSame('C', $soal->kunci_jawaban);
        $this->assertSame('2.50', $soal->bobot_nilai);
        $this->assertTrue($soal->bankSoal->is($bank));
    }

    public function test_jawaban_pilihan_disimpan_huruf_kapital(): void
    {
        $jawaban = JawabanSiswa::factory()->create(['jawaban_pilihan' => 'b']);

        $this->assertSame('B', $jawaban->fresh()->jawaban_pilihan);
    }

    public function test_status_ujian_hanya_dari_kolom_status(): void
    {
        $draft = Ujian::factory()->create();
        $published = Ujian::factory()->published()->create();

        $this->assertFalse($draft->isPublished());
        $this->assertSame('Belum dibuka', $draft->status_label);
        $this->assertTrue($published->isDibuka());
        $this->assertSame('Tersedia', $published->status_label);
        $this->assertSame([$published->id], Ujian::published()->pluck('id')->all());
    }

    public function test_status_sesi_berakhir(): void
    {
        $sesi = SesiUjian::factory()->berlangsung()->create();
        $this->assertTrue($sesi->isBerlangsung());
        $this->assertFalse($sesi->isBerakhir());

        $sesi->update(['status' => SesiUjian::STATUS_TIMEOUT]);
        $this->assertTrue($sesi->isBerakhir());
    }

    public function test_proktor_event_label_dan_severity(): void
    {
        $pelanggaran = ProktorEvent::factory()->create(['tipe' => 'devtools_open']);
        $aksi = ProktorEvent::factory()->create(['tipe' => 'disqualify', 'user_id' => User::factory()->create()->id]);

        $this->assertSame('Buka DevTools', $pelanggaran->tipe_label);
        $this->assertSame('critical', $pelanggaran->severity);
        $this->assertTrue($pelanggaran->isPelanggaran());
        $this->assertSame('Diskualifikasi', $aksi->tipe_label);
        $this->assertFalse($aksi->isPelanggaran());
        $this->assertNotNull($aksi->created_at);
        $this->assertSame([$pelanggaran->id], ProktorEvent::pelanggaran()->pluck('id')->all());
    }

    // --- Fase periode -------------------------------------------------------

    public function test_fase_aktif_dihitung_dari_tanggal(): void
    {
        $periode = PeriodeUjian::factory()->create();
        FasePeriode::factory()->for($periode, 'periode')->tipe('input_soal', 1)->lampau()->create();
        FasePeriode::factory()->for($periode, 'periode')->tipe('pelaksanaan', 2)->create();
        FasePeriode::factory()->for($periode, 'periode')->tipe('remedial', 3)->mendatang()->create();

        $this->assertSame('pelaksanaan', $periode->fase_aktif);
        $this->assertTrue($periode->isFaseAktif('pelaksanaan'));
    }

    public function test_paksa_buka_menang_atas_fase_aktif_berdasarkan_tanggal(): void
    {
        $periode = PeriodeUjian::factory()->create();
        FasePeriode::factory()->for($periode, 'periode')->tipe('pelaksanaan', 1)->create();
        $remedial = FasePeriode::factory()->for($periode, 'periode')->tipe('remedial', 2)->mendatang()->create();

        $remedial->paksaBuka(User::factory()->create());

        $this->assertSame('remedial', $periode->fresh()->fase_aktif);
    }

    public function test_paksa_tutup_dan_reset_override(): void
    {
        $admin = User::factory()->create();
        $fase = FasePeriode::factory()->create();

        $fase->paksaTutup($admin);
        $this->assertFalse($fase->isAktif());
        $this->assertSame('Dipaksa Tutup', $fase->status_label);
        $this->assertTrue($fase->overrideOleh->is($admin));
        $this->assertNull($fase->periode->fresh()->fase_aktif);

        $fase->resetOverride();
        $this->assertTrue($fase->fresh()->isAktif());
        $this->assertSame('pelaksanaan', $fase->periode->fresh()->fase_aktif);
    }

    public function test_scope_aktif_menyertakan_fase_tanpa_override(): void
    {
        $aktifByTanggal = FasePeriode::factory()->create();
        $dipaksaBuka = FasePeriode::factory()->lampau()->create(['override_status' => 'paksa_buka']);
        FasePeriode::factory()->create(['override_status' => 'paksa_tutup']);
        FasePeriode::factory()->lampau()->create();

        $ids = FasePeriode::aktif()->pluck('id')->sort()->values()->all();

        $this->assertSame([$aktifByTanggal->id, $dipaksaBuka->id], $ids);
    }
}
