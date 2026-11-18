<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UjianLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.ujian-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $this->ensureIsNotRateLimited($request);

        $nis = trim($validated['nis']);
        $siswa = Siswa::with('user')
            ->where('nisn', $nis)
            ->first();

        if (! $siswa || ! $siswa->user || (int) $siswa->user->role !== 2) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'nis' => 'NIS atau password tidak sesuai.',
            ]);
        }

        if (! Auth::attempt(['username' => $siswa->user->username, 'password' => $validated['password']], $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'nis' => 'NIS atau password tidak sesuai.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();

        return redirect()->intended(route('ujian.siswa.index'));
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'nis' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('nis')).'|ujian|'.$request->ip());
    }
}
