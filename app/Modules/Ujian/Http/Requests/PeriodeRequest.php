<?php

namespace App\Modules\Ujian\Http\Requests;

use App\Modules\Ujian\Models\PeriodeUjian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PeriodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', PeriodeUjian::class);
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'semester_id' => ['required', 'integer', Rule::exists('semesters', 'id')],
            'status' => ['required', Rule::in(PeriodeUjian::STATUS)],
        ];
    }
}
