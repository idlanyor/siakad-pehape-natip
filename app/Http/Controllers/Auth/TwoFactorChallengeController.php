<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorChallengeController extends Controller
{
    public function __construct(protected TwoFactorService $twoFactor) {}

    /**
     * Tampilkan form tantangan OTP (hanya jika ada user pending dari login).
     */
    public function create(Request $request)
    {
        if (! $request->session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verifikasi kode OTP atau recovery code, lalu selesaikan login.
     */
    public function store(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code'          => 'required_without:recovery_code|nullable|string',
            'recovery_code' => 'required_without:code|nullable|string',
        ]);

        $user = User::findOrFail($userId);

        // Jalur 1: kode OTP dari aplikasi authenticator.
        if ($request->filled('code')) {
            if (! $this->twoFactor->verify($user->two_factor_secret, $request->code)) {
                throw ValidationException::withMessages(['code' => 'Kode OTP salah.']);
            }
        }
        // Jalur 2: recovery code (sekali pakai).
        elseif ($request->filled('recovery_code')) {
            $codes = $user->two_factor_recovery_codes ?? [];
            $input = trim($request->recovery_code);

            if (! in_array($input, $codes, true)) {
                throw ValidationException::withMessages(['recovery_code' => 'Recovery code tidak valid.']);
            }

            // Konsumsi recovery code yang dipakai.
            $user->forceFill([
                'two_factor_recovery_codes' => array_values(array_diff($codes, [$input])),
            ])->save();
        }

        $remember = (bool) $request->session()->get('2fa:remember', false);

        $request->session()->forget(['2fa:user:id', '2fa:remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
