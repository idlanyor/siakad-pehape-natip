<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFormRequestTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 0]);
    }

    public function test_store_siswa_valid_berhasil(): void
    {
        $jurusan = Jurusan::create(['nama_jurusan' => 'TKJ']);

        $this->actingAs($this->admin())->post(route('admin.siswa.store'), [
            'name'          => 'Siswa Uji',
            'username'      => 'siswa.uji',
            'password'      => 'rahasia123',
            'nisn'          => '1234567890',
            'jurusan_id'    => $jurusan->id,
            'tanggal_lahir' => '2008-05-01',
        ])->assertRedirect(route('admin.siswa.index'));

        $this->assertDatabaseHas('users', ['username' => 'siswa.uji', 'role' => 2]);
        $this->assertDatabaseHas('siswas', ['nisn' => '1234567890']);
    }

    public function test_store_siswa_tanpa_field_wajib_gagal(): void
    {
        $this->actingAs($this->admin())->post(route('admin.siswa.store'), [])
            ->assertSessionHasErrors(['name', 'username', 'password', 'nisn', 'jurusan_id', 'tanggal_lahir']);
    }

    public function test_store_siswa_username_duplikat_gagal(): void
    {
        $jurusan = Jurusan::create(['nama_jurusan' => 'TKJ']);
        User::factory()->create(['username' => 'kembar']);

        $this->actingAs($this->admin())->post(route('admin.siswa.store'), [
            'name'          => 'X',
            'username'      => 'kembar',
            'password'      => 'rahasia123',
            'nisn'          => '999',
            'jurusan_id'    => $jurusan->id,
            'tanggal_lahir' => '2008-05-01',
        ])->assertSessionHasErrors('username');
    }

    public function test_non_admin_ditolak_form_request(): void
    {
        $jurusan = Jurusan::create(['nama_jurusan' => 'TKJ']);
        $siswaUser = User::factory()->create(['role' => 2]);

        // authorize() FormRequest menolak non-admin → 403.
        $this->actingAs($siswaUser)->post(route('admin.siswa.store'), [
            'name' => 'X', 'username' => 'y', 'password' => 'rahasia123',
            'nisn' => '5', 'jurusan_id' => $jurusan->id, 'tanggal_lahir' => '2008-01-01',
        ])->assertForbidden();
    }

    public function test_update_guru_boleh_password_kosong(): void
    {
        $admin = $this->admin();
        $guruUser = User::factory()->create(['role' => 1, 'username' => 'guru1']);
        $guru = \App\Models\Guru::create(['user_id' => $guruUser->id, 'nama' => 'Guru Satu']);

        $this->actingAs($admin)->put(route('admin.guru.update', $guru), [
            'name'     => 'Guru Satu Edit',
            'username' => 'guru1', // sama → harus lolos (ignore self)
            'password' => '',       // kosong → tidak diubah
        ])->assertRedirect(route('admin.guru.index'));

        $this->assertDatabaseHas('gurus', ['id' => $guru->id, 'nama' => 'Guru Satu Edit']);
    }
}
