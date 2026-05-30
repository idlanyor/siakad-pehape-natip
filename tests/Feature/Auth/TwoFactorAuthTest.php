<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    private function enable2fa(User $user, string $secret, array $recovery = []): void
    {
        $user->forceFill([
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => $recovery,
            'two_factor_enabled'        => true,
            'two_factor_confirmed_at'   => now(),
        ])->save();
    }

    public function test_login_tanpa_2fa_langsung_masuk(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['username' => $user->username, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_dengan_2fa_dialihkan_ke_challenge_belum_login(): void
    {
        $svc = app(TwoFactorService::class);
        $secret = $svc->generateSecret();
        $user = User::factory()->create();
        $this->enable2fa($user, $secret);

        $this->post('/login', ['username' => $user->username, 'password' => 'password'])
            ->assertRedirect(route('2fa.challenge'));

        // Penting: belum benar-benar login sampai OTP diverifikasi.
        $this->assertGuest();
    }

    public function test_challenge_dengan_otp_benar_menyelesaikan_login(): void
    {
        $svc = app(TwoFactorService::class);
        $secret = $svc->generateSecret();
        $user = User::factory()->create();
        $this->enable2fa($user, $secret);

        // Mulai alur login → set session pending.
        $this->post('/login', ['username' => $user->username, 'password' => 'password']);
        $this->assertGuest();

        $otp = (new \PragmaRX\Google2FAQRCode\Google2FA())->getCurrentOtp($secret);

        $this->post('/two-factor-challenge', ['code' => $otp])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_challenge_dengan_otp_salah_ditolak(): void
    {
        $svc = app(TwoFactorService::class);
        $secret = $svc->generateSecret();
        $user = User::factory()->create();
        $this->enable2fa($user, $secret);

        $this->post('/login', ['username' => $user->username, 'password' => 'password']);

        $this->post('/two-factor-challenge', ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_recovery_code_bisa_dipakai_dan_terkonsumsi(): void
    {
        $svc = app(TwoFactorService::class);
        $secret = $svc->generateSecret();
        $recovery = ['ABC123-DEF456', 'GHI789-JKL012'];
        $user = User::factory()->create();
        $this->enable2fa($user, $secret, $recovery);

        $this->post('/login', ['username' => $user->username, 'password' => 'password']);

        $this->post('/two-factor-challenge', ['recovery_code' => 'ABC123-DEF456'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        // Recovery code yang dipakai harus hilang (sekali pakai).
        $this->assertNotContains('ABC123-DEF456', $user->fresh()->two_factor_recovery_codes);
    }

    public function test_challenge_tanpa_sesi_pending_redirect_ke_login(): void
    {
        $this->get('/two-factor-challenge')->assertRedirect(route('login'));
    }
}
