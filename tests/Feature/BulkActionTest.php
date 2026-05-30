<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkActionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 0]);
    }

    private function buatSiswa(string $nisn): Siswa
    {
        $jurusan = Jurusan::firstOrCreate(['nama_jurusan' => 'TKJ']);
        $user = User::factory()->create(['role' => 2]);
        return Siswa::create([
            'user_id'       => $user->id,
            'jurusan_id'    => $jurusan->id,
            'nama'          => 'Siswa ' . $nisn,
            'nisn'          => $nisn,
            'tanggal_lahir' => '2008-01-01',
        ]);
    }

    private function buatGuru(string $nip): Guru
    {
        $user = User::factory()->create(['role' => 1]);
        return Guru::create([
            'user_id' => $user->id,
            'nip'     => $nip,
            'nama'    => 'Guru ' . $nip,
        ]);
    }

    public function test_bulk_hapus_siswa_menghapus_akun_terpilih(): void
    {
        $a = $this->buatSiswa('1111111111');
        $b = $this->buatSiswa('2222222222');
        $c = $this->buatSiswa('3333333333');

        $this->actingAs($this->admin())
            ->delete(route('admin.siswa.bulk-destroy'), ['ids' => [$a->id, $b->id]])
            ->assertRedirect(route('admin.siswa.index'));

        $this->assertDatabaseMissing('siswas', ['id' => $a->id]);
        $this->assertDatabaseMissing('siswas', ['id' => $b->id]);
        $this->assertDatabaseMissing('users', ['id' => $a->user_id]);
        // Yang tak dipilih tetap ada.
        $this->assertDatabaseHas('siswas', ['id' => $c->id]);
    }

    public function test_bulk_hapus_siswa_tanpa_ids_gagal(): void
    {
        $this->actingAs($this->admin())
            ->delete(route('admin.siswa.bulk-destroy'), ['ids' => []])
            ->assertSessionHasErrors('ids');
    }

    public function test_bulk_hapus_guru_menghapus_akun_terpilih(): void
    {
        $a = $this->buatGuru('1990001');
        $b = $this->buatGuru('1990002');

        $this->actingAs($this->admin())
            ->delete(route('admin.guru.bulk-destroy'), ['ids' => [$a->id]])
            ->assertRedirect(route('admin.guru.index'));

        $this->assertDatabaseMissing('gurus', ['id' => $a->id]);
        $this->assertDatabaseMissing('users', ['id' => $a->user_id]);
        $this->assertDatabaseHas('gurus', ['id' => $b->id]);
    }

    public function test_non_admin_tidak_bisa_bulk_hapus(): void
    {
        $siswa = $this->buatSiswa('9999999999');
        $guru  = User::factory()->create(['role' => 1]);

        $this->actingAs($guru)
            ->delete(route('admin.siswa.bulk-destroy'), ['ids' => [$siswa->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('siswas', ['id' => $siswa->id]);
    }
}
