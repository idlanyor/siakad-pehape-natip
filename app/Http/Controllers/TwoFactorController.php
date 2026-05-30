<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    public function __construct(protected TwoFactorService $twoFactor) {}

    /**
     * Halaman pengaturan 2FA di profil.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Kalau sedang setup (belum confirm), tampilkan QR dari secret pending di session.
        $pendingSecret = session('2fa_pending_secret');
        $qrSvg = null;
        $recoveryCodes = null;

        if ($pendingSecret) {
            $qrSvg = $this->twoFactor->qrCodeSvg(
                config('app.name', 'SIAKAD'),
                $user->email ?: $user->username,
                $pendingSecret
            );
            $recoveryCodes = session('2fa_pending_recovery');
        }

        return view('profile.two-factor', compact('user', 'qrSvg', 'recoveryCodes'));
    }

    /**
     * Mulai aktivasi — generate secret + recovery codes (disimpan sementara di session
     * sampai user konfirmasi dengan kode OTP valid).
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return back()->with('error', '2FA sudah aktif.');
        }

        session([
            '2fa_pending_secret'   => $this->twoFactor->generateSecret(),
            '2fa_pending_recovery' => $this->twoFactor->generateRecoveryCodes(),
        ]);

        return redirect()->route('profile.2fa')->with('success', 'Scan QR code lalu masukkan kode untuk mengaktifkan.');
    }

    /**
     * Konfirmasi OTP → aktifkan 2FA.
     */
    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $secret = session('2fa_pending_secret');
        $recovery = session('2fa_pending_recovery');

        if (! $secret) {
            return redirect()->route('profile.2fa')->with('error', 'Sesi setup kedaluwarsa, ulangi.');
        }

        if (! $this->twoFactor->verify($secret, $request->code)) {
            return back()->with('error', 'Kode OTP salah. Coba lagi.');
        }

        $user = $request->user();
        $user->forceFill([
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => $recovery,
            'two_factor_enabled'        => true,
            'two_factor_confirmed_at'   => now(),
        ])->save();

        session()->forget(['2fa_pending_secret', '2fa_pending_recovery']);

        return redirect()->route('profile.2fa')->with('success', '2FA berhasil diaktifkan. Simpan recovery code Anda!');
    }

    /**
     * Nonaktifkan 2FA — wajib konfirmasi password.
     */
    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        if (! Hash::check($request->password, $request->user()->password)) {
            return back()->with('error', 'Password salah.');
        }

        $request->user()->forceFill([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_enabled'        => false,
            'two_factor_confirmed_at'   => null,
        ])->save();

        return redirect()->route('profile.2fa')->with('success', '2FA telah dinonaktifkan.');
    }
}
