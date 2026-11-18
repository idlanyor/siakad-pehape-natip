<?php

namespace Database\Factories\Ujian;

use App\Modules\Ujian\Models\PeriodeUjian;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PeriodeUjian> */
class PeriodeUjianFactory extends Factory
{
    protected $model = PeriodeUjian::class;

    public function definition(): array
    {
        return [
            'nama' => 'UAS '.fake()->randomElement(['Ganjil', 'Genap']).' 2026/2027',
            'semester_id' => fn () => DataSekolah::semester()->id,
            'status' => 'aktif',
        ];
    }
}
