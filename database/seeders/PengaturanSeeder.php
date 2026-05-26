<?php

namespace Database\Seeders;

use App\Models\Pengaturan;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Pengaturan::DEFAULTS as $item) {
            Pengaturan::firstOrCreate(
                ['key' => $item['key']],
                [
                    'value' => $item['value'],
                    'grup'  => $item['grup'],
                    'label' => $item['label'],
                    'tipe'  => $item['tipe'],
                ]
            );
        }
    }
}
