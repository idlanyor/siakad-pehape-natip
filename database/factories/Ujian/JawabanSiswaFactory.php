<?php

namespace Database\Factories\Ujian;

use App\Modules\Ujian\Models\JawabanSiswa;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\SoalUjian;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JawabanSiswa> */
class JawabanSiswaFactory extends Factory
{
    protected $model = JawabanSiswa::class;

    public function definition(): array
    {
        return [
            'sesi_ujian_id' => SesiUjian::factory(),
            'soal_ujian_id' => fn (array $attr) => SoalUjian::factory()->create([
                'ujian_id' => SesiUjian::find($attr['sesi_ujian_id'])->ujian_id,
            ])->id,
            'urutan' => fake()->unique()->numberBetween(1, 10000),
            'jawaban_pilihan' => 'A',
        ];
    }
}
