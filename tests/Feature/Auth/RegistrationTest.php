<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Registrasi publik dinonaktifkan — pendaftaran siswa baru hanya
     * lewat alur PPDB. Route /register dialihkan ke /login.
     */
    public function test_registration_screen_redirects_to_login(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect(route('login'));
    }

    public function test_public_registration_is_disabled(): void
    {
        // Endpoint POST /register sudah dimatikan; tidak ada user terbuat.
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }
}
