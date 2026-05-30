<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Pastikan maintenance dinonaktifkan setelah test (jangan ganggu test lain).
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
        }
        parent::tearDown();
    }

    public function test_admin_bisa_aktifkan_maintenance(): void
    {
        $admin = User::factory()->create(['role' => 0]);

        $this->actingAs($admin)
            ->post(route('admin.maintenance.enable'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue(app()->isDownForMaintenance());
    }

    public function test_admin_bisa_nonaktifkan_maintenance(): void
    {
        Artisan::call('down', ['--secret' => 'test']);
        $this->assertTrue(app()->isDownForMaintenance());

        $admin = User::factory()->create(['role' => 0]);

        // Bypass middleware maintenance buat test ini (admin sudah punya bypass URL di real usage).
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class)
            ->actingAs($admin)
            ->post(route('admin.maintenance.disable'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse(app()->isDownForMaintenance());
    }

    public function test_non_admin_dapat_503_saat_maintenance(): void
    {
        Artisan::call('down', ['--secret' => 'bypass123']);

        $siswa = User::factory()->create(['role' => 2]);

        // Non-admin (tanpa bypass secret) dapat 503.
        $this->actingAs($siswa)
            ->get('/')
            ->assertStatus(503)
            ->assertSee('503')
            ->assertSee('Layanan Sedang Pemeliharaan');
    }

    public function test_status_endpoint_kembalikan_maintenance_state_saat_up(): void
    {
        $admin = User::factory()->create(['role' => 0]);

        // Saat UP, status endpoint bisa diakses normal.
        $this->actingAs($admin)
            ->getJson(route('admin.maintenance.status'))
            ->assertOk()
            ->assertJson(['maintenance' => false, 'secret' => null]);
    }

    public function test_enable_maintenance_menghasilkan_bypass_secret(): void
    {
        $admin = User::factory()->create(['role' => 0]);

        $response = $this->actingAs($admin)
            ->post(route('admin.maintenance.enable'))
            ->assertRedirect()
            ->assertSessionHas('success');

        // Flash message harus berisi bypass URL.
        $flash = session('success');
        $this->assertStringContainsString('Bypass URL:', $flash);
        $this->assertStringContainsString('?secret=', $flash);
    }
}
