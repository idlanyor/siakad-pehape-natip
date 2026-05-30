<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstallController extends Controller
{
    /** Langkah 1: cek persyaratan sistem. */
    public function welcome()
    {
        $requirements = [
            'PHP >= 8.2'        => version_compare(PHP_VERSION, '8.2.0', '>='),
            'PDO Extension'     => extension_loaded('pdo'),
            'Mbstring'          => extension_loaded('mbstring'),
            'OpenSSL'           => extension_loaded('openssl'),
            'Tokenizer'         => extension_loaded('tokenizer'),
            'cURL'              => extension_loaded('curl'),
            'GD / Imagick'      => extension_loaded('gd') || extension_loaded('imagick'),
            'storage/ writable' => is_writable(storage_path()),
            '.env writable'     => is_writable(base_path('.env')) || is_writable(base_path()),
        ];

        return view('install.welcome', compact('requirements'));
    }

    /** Langkah 2: form konfigurasi database. */
    public function database()
    {
        return view('install.database');
    }

    /** Simpan konfigurasi DB ke .env + tes koneksi. */
    public function saveDatabase(Request $request)
    {
        $data = $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
            'app_name'    => 'required|string|max:100',
            'app_url'     => 'required|url',
        ]);

        // Tes koneksi sebelum menyimpan.
        try {
            config([
                'database.connections.install_test' => [
                    'driver'   => 'mysql',
                    'host'     => $data['db_host'],
                    'port'     => $data['db_port'],
                    'database' => $data['db_database'],
                    'username' => $data['db_username'],
                    'password' => $data['db_password'] ?? '',
                ],
            ]);
            DB::connection('install_test')->getPdo();
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Koneksi database gagal: ' . $e->getMessage());
        }

        $this->writeEnv([
            'APP_NAME'    => '"' . $data['app_name'] . '"',
            'APP_URL'     => $data['app_url'],
            'DB_HOST'     => $data['db_host'],
            'DB_PORT'     => $data['db_port'],
            'DB_DATABASE' => $data['db_database'],
            'DB_USERNAME' => $data['db_username'],
            'DB_PASSWORD' => $data['db_password'] ?? '',
        ]);

        return redirect()->route('install.admin');
    }

    /** Langkah 3: form identitas sekolah + akun admin. */
    public function admin()
    {
        return view('install.admin');
    }

    /** Jalankan migrasi + seed + buat admin + tandai terpasang. */
    public function finish(Request $request)
    {
        $data = $request->validate([
            'sekolah_nama' => 'required|string|max:150',
            'admin_name'   => 'required|string|max:100',
            'admin_username' => 'required|string|max:50',
            'admin_email'  => 'required|email',
            'admin_password' => 'required|string|min:6|confirmed',
        ]);

        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--class' => 'PengaturanSeeder', '--force' => true]);

            \App\Models\Pengaturan::updateOrCreate(
                ['key' => 'sekolah_nama'],
                ['value' => $data['sekolah_nama'], 'grup' => 'umum', 'label' => 'Nama Sekolah', 'tipe' => 'text']
            );

            \App\Models\User::create([
                'name'     => $data['admin_name'],
                'username' => $data['admin_username'],
                'email'    => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'role'     => 0,
            ]);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Instalasi gagal: ' . $e->getMessage());
        }

        // Tandai sudah terpasang → kunci wizard.
        file_put_contents(storage_path('installed'), now()->toDateTimeString());

        return view('install.finish');
    }

    /** Tulis/replace key di file .env. */
    private function writeEnv(array $values): void
    {
        $path = base_path('.env');
        $env = file_exists($path) ? file_get_contents($path) : '';

        foreach ($values as $key => $value) {
            $line = $key . '=' . $value;
            if (preg_match("/^{$key}=.*$/m", $env)) {
                $env = preg_replace("/^{$key}=.*$/m", $line, $env);
            } else {
                $env .= PHP_EOL . $line;
            }
        }

        file_put_contents($path, $env);
    }
}
