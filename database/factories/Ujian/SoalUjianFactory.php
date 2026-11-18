<?php

namespace Database\Factories\Ujian;

use App\Modules\Ujian\Models\SoalUjian;
use App\Modules\Ujian\Models\Ujian;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SoalUjian> */
class SoalUjianFactory extends Factory
{
    protected $model = SoalUjian::class;

    public function definition(): array
    {
        return [
            'ujian_id' => Ujian::factory(),
            'nomor' => fake()->unique()->numberBetween(1, 10000),
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => fake()->sentence().'?',
            'opsi_a' => 'Opsi A',
            'opsi_b' => 'Opsi B',
            'opsi_c' => 'Opsi C',
            'opsi_d' => 'Opsi D',
            'kunci_jawaban' => 'A',
            'bobot_nilai' => 1,
        ];
    }

    public function essay(): static
    {
        return $this->state([
            'tipe' => 'essay',
            'opsi_a' => null,
            'opsi_b' => null,
            'opsi_c' => null,
            'opsi_d' => null,
            'kunci_jawaban' => null,
        ]);
    }
}
