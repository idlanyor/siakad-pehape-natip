<?php

namespace Tests\Feature;

use App\Models\OrangTua;
use App\Models\PendaftarPpdb;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Smoke test: buka setiap route GET sebagai tamu, admin, guru, siswa,
 * pendaftar, dan orang tua dengan data seeder; tidak boleh ada error 5xx.
 * Parameter route diisi id record pertama dari model yang di-type-hint.
 *
 * CRAWL_TULIS=1 juga mengirim POST/PUT/PATCH kosong (harus ditolak validasi,
 * bukan 500). Route maintenance & logout dilewati.
 */
class SmokeSemuaHalamanTest extends TestCase
{
    use RefreshDatabase;

    public function test_tidak_ada_halaman_yang_error_500(): void
    {
        $this->seed();
        $this->withoutVite();
        Storage::fake('public');

        // Seeder tidak membuat akun orang tua & pendaftar; buat satu masing-masing.
        $ortu = User::factory()->create(['role' => 4]);
        $o = OrangTua::create(['user_id' => $ortu->id, 'nama' => 'Ortu Demo']);
        $o->siswas()->attach(Siswa::first()->id);
        $pendaftar = User::factory()->create(['role' => 3]);
        PendaftarPpdb::query()->whereNull('user_id')->first()?->update(['user_id' => $pendaftar->id]);

        $peran = ['tamu' => null];
        foreach ([0 => 'admin', 1 => 'guru', 2 => 'siswa', 3 => 'pendaftar', 4 => 'orangtua'] as $role => $nama) {
            $peran[$nama] = User::where('role', $role)->when($role === 1, fn ($q) => $q->whereHas('guru'))
                ->when($role === 2, fn ($q) => $q->whereHas('siswa'))->first();
        }

        $hasil = [];
        foreach (app('router')->getRoutes() as $route) {
            /** @var Route $route */
            $metode = collect($route->methods())->first(fn ($m) => in_array($m, ['POST', 'PUT', 'PATCH']));
            if (! getenv('CRAWL_TULIS')) {
                $metode = in_array('GET', $route->methods()) ? 'GET' : null;
            }
            if (! $metode || Str::startsWith($route->uri(), ['_', 'sanctum', 'up', 'storage', 'install'])
                || Str::contains($route->uri(), ['maintenance', 'logout'])) {
                continue;
            }
            $uri = preg_replace_callback('/\{(\w+)\??\}/', function ($m) use ($route) {
                $model = $this->modelUntuk($route, $m[1]);

                return $model ? ($model::query()->value((new $model)->getRouteKeyName()) ?? 1) : 1;
            }, $route->uri());

            foreach ($peran as $nama => $user) {
                if ($nama !== 'tamu' && ! $user) {
                    continue;
                }
                $nama === 'tamu' ? app('auth')->forgetGuards() : $this->actingAs($user);
                $res = $metode === 'GET'
                    ? $this->get('/'.ltrim($uri, '/'))
                    : $this->call($metode, '/'.ltrim($uri, '/'), [], [], [], ['HTTP_ACCEPT' => 'text/html']);
                if ($res->getStatusCode() >= 500) {
                    $e = $res->exception;
                    $hasil[] = sprintf('%s %s [%s] %s: %s @ %s:%d', $nama, $metode, $res->getStatusCode(), '/'.$uri,
                        $e ? Str::limit($e->getMessage(), 180) : '?', $e ? str_replace(base_path().'/', '', $e->getFile()) : '', $e?->getLine() ?? 0);
                }
            }
        }

        $this->assertFalse(file_exists(base_path('storage/framework/down')), 'Test menulis ke storage proyek asli.');
        $this->assertSame([], $hasil, count($hasil)." halaman error:\n".implode("\n", $hasil));
    }

    private function modelUntuk(Route $route, string $param): ?string
    {
        $action = $route->getActionName();
        if (str_contains($action, '@')) {
            [$kelas, $metode] = explode('@', $action);
            if (method_exists($kelas, $metode)) {
                foreach ((new \ReflectionMethod($kelas, $metode))->getParameters() as $p) {
                    $t = $p->getType();
                    if ($t instanceof \ReflectionNamedType && $p->getName() === $param && is_subclass_of($t->getName(), Model::class)) {
                        return $t->getName();
                    }
                }
            }
        }

        return null;
    }
}
