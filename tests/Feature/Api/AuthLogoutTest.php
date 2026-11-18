<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_mencabut_token_bearer(): void
    {
        $user = User::factory()->create(['role' => 2]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/logout')->assertOk();

        $this->assertSame(0, $user->tokens()->count());
    }

    /** Regresi: sebelumnya 500 karena TransientToken tidak punya delete(). */
    public function test_logout_dengan_autentikasi_session_tidak_error(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 2]));

        $this->postJson('/api/v1/logout')->assertOk();
    }
}
