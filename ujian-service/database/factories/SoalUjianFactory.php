<?php

namespace Database\Factories;

use App\Models\SoalUjian;
use App\Models\Ujian;
use Illuminate\Database\Eloquent\Factories\Factory;

class SoalUjianFactory extends Factory
{
    protected $model = SoalUjian::class;

    public function definition(): array
    {
        return [
            'ujian_id' => Ujian::factory(),
            'nomor' => fake()->unique()->numberBetween(1, 100),
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => fake()->sentence() . '?',
            'opsi_a' => fake()->word(),
            'opsi_b' => fake()->word(),
            'opsi_c' => fake()->word(),
            'opsi_d' => fake()->word(),
            'opsi_e' => fake()->word(),
            'kunci_jawaban' => 'A',
            'bobot_nilai' => 1,
        ];
    }
}
