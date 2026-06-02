<?php

namespace Database\Factories;

use App\Models\SesiUjian;
use App\Models\Ujian;
use Illuminate\Database\Eloquent\Factories\Factory;

class SesiUjianFactory extends Factory
{
    protected $model = SesiUjian::class;

    public function definition(): array
    {
        return [
            'ujian_id' => Ujian::factory(),
            'siswa_id' => fake()->unique()->numberBetween(1, 10000),
            'siswa_nama' => fake()->name(),
            'siswa_nis' => fake()->numerify('#####'),
            'kelas_id' => fake()->numberBetween(1, 12),
            'status' => 'belum',
            'is_resmi' => true,
            'fase' => 'pelaksanaan',
            'waktu_mulai' => null,
            'waktu_selesai' => null,
            'durasi_detik' => 0,
            'tab_switch_count' => 0,
        ];
    }
}
