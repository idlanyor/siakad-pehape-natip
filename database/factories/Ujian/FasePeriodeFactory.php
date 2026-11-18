<?php

namespace Database\Factories\Ujian;

use App\Modules\Ujian\Models\FasePeriode;
use App\Modules\Ujian\Models\PeriodeUjian;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FasePeriode> */
class FasePeriodeFactory extends Factory
{
    protected $model = FasePeriode::class;

    public function definition(): array
    {
        return [
            'periode_id' => PeriodeUjian::factory(),
            'tipe' => 'pelaksanaan',
            'urutan' => 1,
            'tgl_mulai' => now()->subDay(),
            'tgl_selesai' => now()->addDay(),
        ];
    }

    public function tipe(string $tipe, int $urutan): static
    {
        return $this->state(['tipe' => $tipe, 'urutan' => $urutan]);
    }

    public function lampau(): static
    {
        return $this->state(['tgl_mulai' => now()->subDays(10), 'tgl_selesai' => now()->subDays(5)]);
    }

    public function mendatang(): static
    {
        return $this->state(['tgl_mulai' => now()->addDays(5), 'tgl_selesai' => now()->addDays(10)]);
    }
}
