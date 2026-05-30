<?php

namespace Tests\Feature;

use Tests\TestCase;

class InstallerTest extends TestCase
{
    public function test_installer_terkunci_jika_sudah_terpasang(): void
    {
        // Lingkungan test punya marker palsu sementara.
        $marker = storage_path('installed');
        $created = false;
        if (! file_exists($marker)) {
            file_put_contents($marker, 'test');
            $created = true;
        }

        try {
            $this->get('/install')->assertRedirect('/');
        } finally {
            if ($created) {
                @unlink($marker);
            }
        }
    }
}
