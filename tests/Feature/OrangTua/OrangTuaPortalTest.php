<?php

namespace Tests\Feature\OrangTua;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrangTuaPortalTest extends TestCase
{
    use RefreshDatabase;

    private function buatAnak(string $nama = 'Anak Satu'): Siswa
    {
        $jurusan = Jurusan::create(['nama_jurusan' => 'RPL']);
        $kelas = Kelas::create(['nama_kelas' => 'X RPL 1', 'jurusan_id' => $jurusan->id, 'tingkat' => 'X']);
        $userSiswa = User::factory()->create(['role' => 2]);

        return Siswa::create([
            'user_id'       => $userSiswa->id,
            'jurusan_id'    => $jurusan->id,
            'kelas_id'      => $kelas->id,
            'nama'          => $nama,
            'nisn'          => (string) fake()->unique()->numerify('##########'),
            'tanggal_lahir' => '2008-01-01',
        ]);
    }

    private function buatOrangTua(Siswa $anak): User
    {
        $user = User::factory()->create(['role' => 4]);
        $ortu = OrangTua::create(['user_id' => $user->id, 'nama' => $user->name]);
        $ortu->siswas()->attach($anak->id, ['hubungan' => 'ayah']);

        return $user;
    }

    public function test_orangtua_bisa_akses_dashboard(): void
    {
        $anak = $this->buatAnak();
        $user = $this->buatOrangTua($anak);

        $this->actingAs($user)
            ->get('/orangtua/dashboard')
            ->assertOk()
            ->assertSee($anak->nama);
    }

    public function test_orangtua_bisa_lihat_semua_menu_anak(): void
    {
        $anak = $this->buatAnak();
        $user = $this->buatOrangTua($anak);

        foreach (['nilai', 'absensi', 'kesiswaan', 'spp'] as $menu) {
            $this->actingAs($user)->get("/orangtua/{$menu}")->assertOk();
        }
    }

    public function test_orangtua_tidak_bisa_lihat_tagihan_anak_orang_lain(): void
    {
        $anakSendiri = $this->buatAnak('Anak Sendiri');
        $user = $this->buatOrangTua($anakSendiri);

        $anakOrangLain = $this->buatAnak('Anak Orang Lain');
        $tagihanAsing = TagihanSpp::create([
            'siswa_id' => $anakOrangLain->id,
            'bulan'    => '2026-01-01',
            'nominal'  => 150000,
            'status'   => 'belum',
        ]);

        $this->actingAs($user)
            ->get("/orangtua/spp/{$tagihanAsing->id}")
            ->assertForbidden();
    }

    public function test_non_orangtua_ditolak(): void
    {
        $siswaUser = User::factory()->create(['role' => 2]);

        $this->actingAs($siswaUser)
            ->get('/orangtua/dashboard')
            ->assertForbidden();
    }
}
