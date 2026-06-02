<?php

namespace Tests\Unit;

use App\Http\Middleware\InjectSiswaAuthContext;
use App\Models\Siswa;
use App\Models\UserSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class InjectSiswaAuthContextTest extends TestCase
{
    public function test_injects_auth_user_for_logged_in_siswa(): void
    {
        $siswa = Siswa::create([
            'siakad_id' => 1, 'nis' => '12345', 'nisn' => '999',
            'nama_lengkap' => 'Budi', 'kelas_id' => 7, 'kelas_nama' => 'X-A',
            'tingkat' => 10, 'username' => '12345', 'status' => 'aktif',
        ]);
        $user = UserSiswa::create([
            'siswa_id' => $siswa->id, 'username' => '12345',
            'password' => bcrypt('12345'), 'is_active' => true,
        ]);
        Auth::guard('siswa')->login($user);

        $request = Request::create('/siswa/ujian', 'GET');
        $request->setLaravelSession($this->app['session']->driver());

        $captured = null;
        (new InjectSiswaAuthContext())->handle($request, function ($req) use (&$captured) {
            $captured = $req->input('_auth_user');
            return response('ok');
        });

        $this->assertSame($siswa->id, $captured['user_id']);
        $this->assertSame('siswa', $captured['role']);
        $this->assertSame(7, $captured['kelas_id']);
        $this->assertSame('Budi', $captured['nama']);
        $this->assertSame('12345', $captured['nis']);
    }

    public function test_noop_when_not_logged_in(): void
    {
        $request = Request::create('/siswa/ujian', 'GET');
        $captured = 'unset';
        (new InjectSiswaAuthContext())->handle($request, function ($req) use (&$captured) {
            $captured = $req->input('_auth_user');
            return response('ok');
        });
        $this->assertNull($captured);
    }
}
