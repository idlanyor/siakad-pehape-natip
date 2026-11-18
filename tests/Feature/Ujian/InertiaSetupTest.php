<?php

namespace Tests\Feature\Ujian;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InertiaSetupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/ujian')->assertRedirect(route('login'));
    }

    public static function roleProvider(): array
    {
        return [
            'admin' => [0, 'admin'],
            'guru' => [1, 'guru'],
            'siswa' => [2, 'siswa'],
        ];
    }

    #[DataProvider('roleProvider')]
    public function test_user_login_melihat_dashboard_inertia(int $role, string $roleName): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->actingAs($user)
            ->get('/ujian')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('auth.user.id', $user->id)
                ->where('auth.user.role', $roleName)
            );
    }

    public function test_halaman_inertia_memakai_shell_adminlte(): void
    {
        $user = User::factory()->create(['role' => 0]);

        $html = $this->actingAs($user)->get('/ujian')->getContent();

        $this->assertStringContainsString('main-sidebar', $html);
        $this->assertStringContainsString('data-page=', $html);
        $this->assertStringContainsString('<title inertia>', $html);
        // Header konten Blade tidak dirender; Vue yang merender header sendiri.
        $this->assertStringNotContainsString('content-header', $html);
    }

    public function test_ziggy_hanya_mengekspos_route_ujian(): void
    {
        $user = User::factory()->create(['role' => 0]);

        $html = $this->actingAs($user)->get('/ujian')->getContent();

        $this->assertStringContainsString('"ujian.dashboard"', $html);
        $this->assertStringNotContainsString('"admin.siswa.index"', $html);
    }

    public function test_halaman_blade_lain_tidak_terpengaruh(): void
    {
        $user = User::factory()->create(['role' => 0]);

        $html = $this->actingAs($user)->get(route('admin.dashboard'))->getContent();

        $this->assertStringContainsString('content-header', $html);
        $this->assertStringNotContainsString('data-page=', $html);
    }
}
