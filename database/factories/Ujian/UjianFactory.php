<?php

namespace Database\Factories\Ujian;

use App\Modules\Ujian\Models\PeriodeUjian;
use App\Modules\Ujian\Models\Ujian;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ujian> */
class UjianFactory extends Factory
{
    protected $model = Ujian::class;

    public function definition(): array
    {
        return [
            'periode_id' => PeriodeUjian::factory(),
            'judul' => 'Ujian '.fake()->words(2, true),
            'tipe' => 'uas',
            'mapel_id' => fn () => DataSekolah::mapel()->id,
            'kelas_id' => fn () => DataSekolah::kelas()->id,
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'status' => Ujian::STATUS_DRAFT,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => Ujian::STATUS_PUBLISHED]);
    }
}
