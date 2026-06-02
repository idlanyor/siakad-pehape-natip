<?php

namespace Database\Factories;

use App\Models\PeriodeUjian;
use Illuminate\Database\Eloquent\Factories\Factory;

class PeriodeUjianFactory extends Factory
{
    protected $model = PeriodeUjian::class;

    public function definition(): array
    {
        return [
            'nama' => 'Periode Ujian ' . fake()->words(2, true),
            'tahun_ajaran' => '2025/2026',
            'semester' => fake()->randomElement(['ganjil', 'genap']),
            'status' => 'aktif',
            'fase_aktif' => 'pelaksanaan',
        ];
    }
}
