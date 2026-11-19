<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PengaturanSeeder::class);
        $this->call(StrukturOrganisasiSeeder::class);

        // Data demo membuat akun dengan password "password". Jangan pernah di produksi:
        // pasang lewat installer web (/install) yang membuat satu admin dengan password pilihan sendiri.
        if (app()->isProduction()) {
            $this->command?->warn('APP_ENV=production: data & akun demo dilewati.');

            return;
        }

        $this->call(DummyDataSeeder::class);
        $this->call(LengkapSeeder::class);
    }
}
