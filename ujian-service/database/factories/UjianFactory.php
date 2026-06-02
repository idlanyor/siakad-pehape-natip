<?php

namespace Database\Factories;

use App\Models\PeriodeUjian;
use App\Models\Ujian;
use Illuminate\Database\Eloquent\Factories\Factory;

class UjianFactory extends Factory
{
    protected $model = Ujian::class;

    public function definition(): array
    {
        $mulai = now()->addDay();

        return [
            'periode_id' => PeriodeUjian::factory(),
            'judul' => 'Ujian ' . fake()->words(2, true),
            'deskripsi' => fake()->sentence(),
            'tipe' => 'quiz',
            'mapel_id' => fake()->numberBetween(1, 20),
            'mapel_nama' => fake()->randomElement(['Matematika', 'Bahasa Indonesia', 'IPA', 'IPS']),
            'kelas_id' => fake()->numberBetween(1, 12),
            'kelas_nama' => fake()->randomElement(['X A', 'XI B', 'XII C']),
            'guru_id' => fake()->numberBetween(1, 20),
            'guru_nama' => fake()->name(),
            'waktu_mulai' => $mulai,
            'waktu_selesai' => $mulai->copy()->addHours(2),
            'durasi_menit' => 90,
            'passing_grade' => 75,
            'jumlah_soal' => 10,
            'acak_soal' => false,
            'acak_opsi' => false,
            'status' => 'draft',
            'is_published' => false,
            'jumlah_peserta' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'is_published' => true,
        ]);
    }
}
