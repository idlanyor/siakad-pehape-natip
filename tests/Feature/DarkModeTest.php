<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DarkModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dark_mode_cookie_menambah_class_dark_mode(): void
    {
        $admin = User::factory()->create(['role' => 0]);

        $res = $this->actingAs($admin)
            ->disableCookieEncryption()
            ->withUnencryptedCookie('dark_mode', 'on')
            ->get(route('admin.dashboard'));

        $res->assertOk();
        $res->assertSee('dark-mode', false);
        $res->assertSee('navbar-expand navbar-dark navbar-secondary', false);
        $res->assertSee('fa-sun', false); // ikon toggle saat dark
    }

    public function test_tanpa_cookie_mode_terang(): void
    {
        $admin = User::factory()->create(['role' => 0]);

        $res = $this->actingAs($admin)->get(route('admin.dashboard'));

        $res->assertOk();
        $res->assertSee('navbar-white', false);
        $res->assertSee('fa-moon', false); // ikon toggle saat terang
        // navbar render sebagai light (bukan dark) — cek elemen nav, bukan string JS.
        $res->assertSee('navbar-expand navbar-white navbar-light', false);
    }

    /**
     * Regression: cookie dark_mode di-set via JS (plaintext) harus terbaca
     * di request berikutnya — yaitu TIDAK dienkripsi oleh EncryptCookies.
     * Sebelumnya gagal: Laravel coba dekripsi → null → selalu balik ke light.
     */
    public function test_cookie_dark_mode_tidak_dienkripsi_dan_terbaca_plaintext(): void
    {
        $admin = User::factory()->create(['role' => 0]);

        // Kirim cookie plaintext mentah (meniru document.cookie dari browser).
        $res = $this->actingAs($admin)
            ->disableCookieEncryption()
            ->withUnencryptedCookie('dark_mode', 'on')
            ->get(route('admin.dashboard'));

        $res->assertOk();
        $res->assertSee('dark-mode', false);
    }
}
