<?php

namespace Tests\Feature;

use App\Models\GelombangPpdb;
use App\Models\Jurusan;
use App\Models\PendaftarPpdb;
use App\Models\User;
use Database\Factories\Ujian\DataSekolah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PpdbAksesTest extends TestCase
{
    use RefreshDatabase;

    private Jurusan $jurusan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jurusan = Jurusan::create(['nama_jurusan' => 'Rekayasa Perangkat Lunak', 'is_aktif_ppdb' => true]);
        GelombangPpdb::create([
            'nama' => 'Gelombang 1', 'tahun_ajaran' => '2027/2028', 'kuota' => 0, 'is_aktif' => true,
            'tanggal_buka' => now()->subDay(), 'tanggal_tutup' => now()->addWeek(),
        ]);
    }

    private function dataForm(array $tambahan = []): array
    {
        return array_merge([
            'nama' => 'Rina Wulandari', 'jenis_kelamin' => 'P', 'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2011-05-14', 'nisn' => '0112233445', 'alamat' => 'Jl. Melati 5',
            'no_hp' => '081200000000', 'asal_sekolah' => 'SMP Negeri 10', 'jurusan_pilihan_id' => $this->jurusan->id,
        ], $tambahan);
    }

    public function test_pendaftar_melihat_akun_sekali_dan_password_bukan_nisn(): void
    {
        $res = $this->post(route('ppdb.store'), $this->dataForm());
        $pendaftar = PendaftarPpdb::firstOrFail();
        $res->assertRedirect(route('ppdb.sukses', $pendaftar));

        $password = $pendaftar->akun_password_plain;
        $this->assertNotSame('0112233445', $password);
        $this->assertSame(10, strlen($password));
        $this->assertTrue(Hash::check($password, $pendaftar->userAkun->password));

        // Tepat setelah mendaftar: password tampil
        $this->get(route('ppdb.sukses', $pendaftar))->assertOk()->assertSee($password);
        // Dibuka ulang di sesi yang sama: halaman boleh, password tidak lagi tampil
        $this->get(route('ppdb.sukses', $pendaftar))->assertOk()->assertDontSee($password);
        // Bukti cetak tetap bisa dibuka oleh sesi pendaftar
        $this->get(route('ppdb.cetak', $pendaftar))->assertOk()->assertSee($password);
    }

    /** Regresi: ID berurutan memungkinkan siapa pun membuka data akun pendaftar lain. */
    public function test_orang_lain_tidak_bisa_membuka_halaman_sukses_atau_cetak(): void
    {
        $this->post(route('ppdb.store'), $this->dataForm());
        $pendaftar = PendaftarPpdb::firstOrFail();

        $this->flushSession();
        $this->get(route('ppdb.sukses', $pendaftar))->assertNotFound();
        $this->get(route('ppdb.cetak', $pendaftar))->assertNotFound();

        // Pengguna login lain (bukan admin, bukan pemilik) juga ditolak
        $lain = User::factory()->create(['role' => 3]);
        $this->actingAs($lain)->get(route('ppdb.cetak', $pendaftar))->assertNotFound();
    }

    public function test_pemilik_akun_dan_admin_boleh_membuka_bukti(): void
    {
        $this->post(route('ppdb.store'), $this->dataForm());
        $pendaftar = PendaftarPpdb::firstOrFail();
        $this->flushSession();

        $this->actingAs($pendaftar->userAkun)->get(route('ppdb.cetak', $pendaftar))->assertOk();
        $this->actingAs(User::factory()->create(['role' => 0]))->get(route('ppdb.cetak', $pendaftar))->assertOk();
    }

    /** Regresi: $request->all() sebelumnya ikut menyimpan isian sisipan. */
    public function test_field_sisipan_tidak_ikut_tersimpan(): void
    {
        $kelas = DataSekolah::kelas();
        $penyusup = User::factory()->create(['role' => 0]);

        $this->post(route('ppdb.store'), $this->dataForm([
            'status' => 'diterima', 'kelas_diterima_id' => $kelas->id, 'user_id' => $penyusup->id,
            'akun_username' => 'admin', 'catatan_admin' => 'lolos',
        ]));

        $p = PendaftarPpdb::firstOrFail();
        $this->assertSame('pending', $p->status);
        $this->assertNull($p->kelas_diterima_id);
        $this->assertNull($p->catatan_admin);
        $this->assertNotSame($penyusup->id, $p->user_id);
        $this->assertNotSame('admin', $p->akun_username);
    }

    public function test_pesan_gelombang_tutup_tampil_di_formulir(): void
    {
        GelombangPpdb::query()->update(['tanggal_tutup' => now()->subMinute()]);

        $this->from(route('ppdb.index'))->post(route('ppdb.store'), $this->dataForm())
            ->assertRedirect(route('ppdb.index'));
        $this->get(route('ppdb.index'))->assertSee('tidak ada gelombang pendaftaran yang dibuka');
    }
}
