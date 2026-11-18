<?php

namespace Tests\Feature\Ujian;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use App\Modules\Ujian\Models\BankSoal;
use App\Modules\Ujian\Models\PeriodeUjian;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\Ujian;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Guru $guru;

    private Guru $guruLain;

    private Ujian $ujian;

    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 0]);
        $this->guru = DataSekolah::guru();
        $this->guruLain = DataSekolah::guru();
        $this->ujian = Ujian::factory()->published()->create(['guru_id' => $this->guru->id]);
        $this->guru->mapels()->attach($this->ujian->mapel_id);
        $this->siswa = DataSekolah::siswa($this->ujian->kelas);
    }

    public function test_guru_pemilik_mengelola_ujiannya(): void
    {
        $user = $this->guru->user;

        foreach (['view', 'update', 'delete', 'awasi', 'nilai'] as $ability) {
            $this->assertTrue($user->can($ability, $this->ujian), $ability);
        }
        $this->assertFalse($user->can('kerjakan', $this->ujian));
    }

    public function test_guru_lain_tidak_bisa_menyentuh_ujian_orang_lain(): void
    {
        $user = $this->guruLain->user;

        foreach (['view', 'update', 'delete', 'awasi', 'nilai'] as $ability) {
            $this->assertFalse($user->can($ability, $this->ujian), $ability);
        }
    }

    public function test_guru_id_dibandingkan_dengan_id_guru_bukan_id_user(): void
    {
        // Cari pasangan bentrok: guru B yang id-nya sama dengan user_id guru A.
        // Pemeriksaan lama (ujian.guru_id == user id) akan memberi A akses ke ujian B.
        while (($guruB = Guru::find($this->guru->user_id)) === null) {
            DataSekolah::guru();
        }
        $this->assertFalse($guruB->is($this->guru));
        $this->ujian->update(['guru_id' => $guruB->id]);

        $this->assertSame($this->guru->user_id, $this->ujian->guru_id);
        $this->assertFalse($this->guru->user->can('update', $this->ujian));
        $this->assertTrue($guruB->user->can('update', $this->ujian));
    }

    public function test_ujian_dengan_sesi_tidak_bisa_dihapus(): void
    {
        SesiUjian::factory()->create(['ujian_id' => $this->ujian->id, 'siswa_id' => $this->siswa->id]);

        $this->assertFalse($this->guru->user->can('delete', $this->ujian));
        $this->assertTrue($this->admin->can('delete', $this->ujian));
    }

    public function test_guru_hanya_memakai_mapel_yang_diampu(): void
    {
        $this->assertTrue($this->guru->user->can('gunakanMapel', [Ujian::class, $this->ujian->mapel_id]));
        $this->assertFalse($this->guruLain->user->can('gunakanMapel', [Ujian::class, $this->ujian->mapel_id]));
    }

    public function test_siswa_hanya_ujian_terbit_untuk_kelasnya(): void
    {
        $user = $this->siswa->user;
        $this->assertTrue($user->can('kerjakan', $this->ujian));
        $this->assertTrue($user->can('view', $this->ujian));
        $this->assertFalse($user->can('update', $this->ujian));
        $this->assertFalse($user->can('awasi', $this->ujian));

        $this->ujian->update(['status' => Ujian::STATUS_DRAFT]);
        $this->assertFalse($user->can('kerjakan', $this->ujian));

        $this->ujian->update(['status' => Ujian::STATUS_PUBLISHED]);
        $siswaKelasLain = DataSekolah::siswa();
        $this->assertFalse($siswaKelasLain->user->can('kerjakan', $this->ujian));
    }

    public function test_sesi_hanya_dikerjakan_pemiliknya(): void
    {
        $sesi = SesiUjian::factory()->create(['ujian_id' => $this->ujian->id, 'siswa_id' => $this->siswa->id]);
        $siswaLain = DataSekolah::siswa($this->ujian->kelas);

        $this->assertTrue($this->siswa->user->can('kerjakan', $sesi));
        $this->assertTrue($this->siswa->user->can('view', $sesi));
        $this->assertFalse($siswaLain->user->can('kerjakan', $sesi));
        $this->assertFalse($siswaLain->user->can('view', $sesi));
        $this->assertFalse($this->guru->user->can('kerjakan', $sesi));
    }

    public function test_sesi_diawasi_dan_dinilai_guru_pemilik_ujian(): void
    {
        $sesi = SesiUjian::factory()->create(['ujian_id' => $this->ujian->id, 'siswa_id' => $this->siswa->id]);

        foreach (['view', 'awasi', 'nilai'] as $ability) {
            $this->assertTrue($this->guru->user->can($ability, $sesi), $ability);
            $this->assertTrue($this->admin->can($ability, $sesi), $ability);
            $this->assertFalse($this->guruLain->user->can($ability, $sesi), $ability);
        }
    }

    public function test_bank_soal_dipakai_bersama_pengampu_tetapi_diubah_pembuatnya(): void
    {
        $soal = BankSoal::factory()->create([
            'mapel_id' => $this->ujian->mapel_id,
            'created_by' => $this->guruLain->user_id,
        ]);

        $this->assertTrue($this->guru->user->can('view', $soal), 'pengampu mapel');
        $this->assertFalse($this->guru->user->can('update', $soal));
        $this->assertTrue($this->guruLain->user->can('update', $soal), 'pembuat');
        $this->assertTrue($this->admin->can('delete', $soal));
        $this->assertFalse($this->siswa->user->can('view', $soal));

        $guruTanpaMapel = DataSekolah::guru();
        $this->assertFalse($guruTanpaMapel->user->can('view', $soal));
    }

    public function test_periode_dikelola_admin_dilihat_guru(): void
    {
        $this->assertTrue($this->admin->can('create', PeriodeUjian::class));
        $this->assertTrue($this->admin->can('kelolaFase', PeriodeUjian::class));
        $this->assertTrue($this->guru->user->can('viewAny', PeriodeUjian::class));
        $this->assertFalse($this->guru->user->can('create', PeriodeUjian::class));
        $this->assertFalse($this->siswa->user->can('viewAny', PeriodeUjian::class));
    }
}
