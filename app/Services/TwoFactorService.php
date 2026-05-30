<?php

namespace App\Services;

use PragmaRX\Google2FAQRCode\Google2FA;

/**
 * Wrapper Google2FA (TOTP) — generate secret, QR, verifikasi kode,
 * dan recovery codes. Dipakai TwoFactorController & alur login challenge.
 */
class TwoFactorService
{
    protected Google2FA $engine;

    public function __construct()
    {
        $this->engine = new Google2FA();
    }

    public function generateSecret(): string
    {
        return $this->engine->generateSecretKey();
    }

    /**
     * SVG inline QR code untuk di-scan Google Authenticator / Authy.
     */
    public function qrCodeSvg(string $company, string $holder, string $secret): string
    {
        return $this->engine->getQRCodeInline($company, $holder, $secret);
    }

    public function verify(string $secret, string $code): bool
    {
        return (bool) $this->engine->verifyKey($secret, $code);
    }

    /**
     * Generate sekumpulan recovery code (sekali pakai).
     *
     * @return array<int,string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = sprintf(
                '%s-%s',
                strtoupper(bin2hex(random_bytes(3))),
                strtoupper(bin2hex(random_bytes(3)))
            );
        }

        return $codes;
    }
}
