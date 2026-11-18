<?php

namespace App\Modules\Ujian\Http\Requests;

use App\Modules\Ujian\Models\FasePeriode;
use App\Modules\Ujian\Models\PeriodeUjian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('kelolaFase', PeriodeUjian::class);
    }

    public function rules(): array
    {
        $fase = $this->route('fase');
        $periodeId = $fase?->periode_id ?? $this->route('periode')?->id;

        return [
            'tipe' => [
                'required',
                Rule::in(FasePeriode::TIPE),
                Rule::unique('fase_periode', 'tipe')->where('periode_id', $periodeId)->ignore($fase?->id),
            ],
            'urutan' => ['required', 'integer', 'min:1', 'max:20'],
            'tgl_mulai' => ['required', 'date'],
            'tgl_selesai' => ['required', 'date', 'after:tgl_mulai'],
        ];
    }

    public function messages(): array
    {
        return ['tipe.unique' => 'Fase ini sudah ada di periode tersebut.'];
    }
}
