<?php

namespace Database\Factories\Ujian;

use App\Modules\Ujian\Models\BankSoal;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BankSoal> */
class BankSoalFactory extends Factory
{
    protected $model = BankSoal::class;

    public function definition(): array
    {
        return [
            'mapel_id' => fn () => DataSekolah::mapel()->id,
            'topik' => fake()->words(2, true),
            'tingkat_kesulitan' => 'sedang',
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => fake()->sentence().'?',
            'opsi_a' => 'Opsi A',
            'opsi_b' => 'Opsi B',
            'opsi_c' => 'Opsi C',
            'opsi_d' => 'Opsi D',
            'kunci_jawaban' => 'A',
            'bobot_default' => 1,
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
