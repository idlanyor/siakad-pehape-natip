<?php

namespace App\Modules\Ujian\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SoalUjianRequest extends FormRequest
{
    use AturanSoal;

    public function authorize(): bool
    {
        $ujian = $this->route('ujian') ?? $this->route('soal')?->ujian;

        return $ujian !== null && $this->user()->can('update', $ujian);
    }

    public function rules(): array
    {
        return $this->aturanSoal();
    }

    public function atribut(): array
    {
        return [...$this->dataSoal(), 'bobot_nilai' => $this->input('bobot')];
    }
}
