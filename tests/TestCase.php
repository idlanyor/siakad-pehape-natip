<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Bootstrap aplikasi dengan konfigurasi testing yang dipaksakan.
     *
     * Variabel DB_* dari shell environment bisa saja terbawa ke dalam test
     * lewat $_SERVER, sehingga nilai dari phpunit.xml tidak selalu menang.
     * Agar test tidak pernah menyentuh database production, koneksi di sini
     * dipaksa ke SQLite in-memory.
     */
    public function createApplication(): Application
    {
        $this->forceTestingEnvironment();

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->useStoragePath($this->storageSementara());

        $app->make(Kernel::class)->bootstrap();

        $this->guardAgainstProductionDatabase($app);

        return $app;
    }

    /**
     * Paksa nilai environment testing pada $_SERVER/$_ENV/getenv.
     *
     * env() membaca $_SERVER lebih dulu, dan $_SERVER diwarisi dari shell.
     * Karena itu penulisan harus dilakukan sebelum aplikasi di-bootstrap.
     */
    protected function forceTestingEnvironment(): void
    {
        $vars = [
            'APP_ENV' => 'testing',
            'APP_DEBUG' => 'true',
            'BCRYPT_ROUNDS' => '4',
            'CACHE_STORE' => 'array',
            'MAIL_MAILER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'array',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
            'DB_SOCKET' => '',
            'DB_URL' => '',
        ];

        foreach ($vars as $key => $value) {
            $_SERVER[$key] = $value;
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }

    /**
     * Folder storage terpisah per proses test.
     *
     * Tanpa ini test menulis ke storage/ proyek asli: mis. test maintenance
     * membuat storage/framework/down sungguhan (aplikasi ikut 503 bila test
     * gagal di tengah), dan unggahan/log test bercampur dengan data asli.
     */
    protected function storageSementara(): string
    {
        static $path = null;

        if ($path === null) {
            $path = sys_get_temp_dir().'/siakad-test-storage-'.getmypid();
            foreach (['app/public', 'framework/cache/data', 'framework/sessions', 'framework/views', 'framework/testing', 'logs'] as $dir) {
                @mkdir("{$path}/{$dir}", 0775, true);
            }
            register_shutdown_function(static function () use ($path) {
                if (! is_dir($path)) {
                    return;
                }
                $it = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($it as $f) {
                    $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
                }
                @rmdir($path);
            });
        }

        return $path;
    }

    /**
     * Guard: hentikan test bila koneksi database bukan SQLite in-memory.
     */
    protected function guardAgainstProductionDatabase(Application $app): void
    {
        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new \RuntimeException(
                'Test dibatalkan: koneksi database testing bukan SQLite in-memory '
                ."({$connection} / ".var_export($database, true).').'
            );
        }
    }
}
