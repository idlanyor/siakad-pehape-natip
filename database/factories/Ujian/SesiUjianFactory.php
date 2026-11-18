<?php

namespace Database\Factories\Ujian;

use App\Models\Siswa;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\Ujian;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SesiUjian> */
class SesiUjianFactory extends Factory
{
    protected $model = SesiUjian::class;

    public function definition(): array
    {
        return [
            'ujian_id' => Ujian::factory()->published(),
            'siswa_id' => fn (array $attr) => DataSekolah::siswa(
                Ujian::find($attr['ujian_id'])?->kelas
            )->id,
            'kelas_id' => fn (array $attr) => Siswa::find($attr['siswa_id'])?->kelas_id,
            'status' => SesiUjian::STATUS_BELUM,
            'is_resmi' => true,
            'fase' => 'pelaksanaan',
        ];
    }

    public function berlangsung(): static
    {
        return $this->state([
            'status' => SesiUjian::STATUS_BERLANGSUNG,
            'waktu_mulai' => now()->subMinutes(10),
            'last_activity_at' => now(),
        ]);
    }
}
