<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class UjianSsoController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $user = auth()->user();

        $payload = array_filter([
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + (int) config('services.ujian.jwt_ttl', 3600),
            'data' => array_filter([
                'user_id' => $user->id,
                'role' => $this->roleName((int) $user->role),
                'nama' => $user->name,
                'email' => $user->email,
                ...$this->roleSnapshot($user),
                'issued_by' => 'siakad',
            ], fn ($value) => $value !== null),
        ]);

        $token = $this->encodeJwt($payload);
        $baseUrl = rtrim(config('services.ujian.url', 'http://localhost:8001'), '/');

        Log::info('Redirecting user to ujian service via SSO', [
            'user_id' => $user->id,
            'role' => $user->role,
        ]);

        return redirect()->away($baseUrl . '/auth/handoff?token=' . urlencode($token));
    }

    private function roleName(int $role): string
    {
        return match ($role) {
            0 => 'admin',
            1 => 'guru',
            2 => 'siswa',
            default => 'siswa',
        };
    }

    private function roleSnapshot($user): array
    {
        if ((int) $user->role === 2) {
            $siswa = Siswa::where('user_id', $user->id)->first();

            return [
                'siswa_id' => $siswa?->id,
                'nis' => $siswa?->nisn,
                'kelas_id' => $siswa?->kelas_id,
                'jurusan_id' => $siswa?->jurusan_id,
            ];
        }

        if ((int) $user->role === 1) {
            $guru = Guru::where('user_id', $user->id)->first();

            return [
                'guru_id' => $guru?->id,
                'nip' => $guru?->nip,
            ];
        }

        return [];
    }

    private function encodeJwt(array $payload): string
    {
        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $body = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $header . '.' . $body, (string) config('services.ujian.jwt_secret'), true);

        return $header . '.' . $body . '.' . $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
