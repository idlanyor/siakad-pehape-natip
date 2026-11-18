<?php

namespace Database\Factories\Ujian;

use App\Modules\Ujian\Models\ProktorEvent;
use App\Modules\Ujian\Models\SesiUjian;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProktorEvent> */
class ProktorEventFactory extends Factory
{
    protected $model = ProktorEvent::class;

    public function definition(): array
    {
        return [
            'sesi_ujian_id' => SesiUjian::factory(),
            'tipe' => 'tab_switch',
        ];
    }
}
