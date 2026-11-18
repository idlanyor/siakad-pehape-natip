<?php

namespace App\Modules\Ujian\Http\Requests;

use App\Modules\Ujian\Models\BankSoal;
use App\Modules\Ujian\Models\Ujian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BankSoalRequest extends FormRequest
{
    use AturanSoal {
        withValidator as validasiSoal;
    }

    public function authorize(): bool
    {
        $soal = $this->route('bankSoal');

        return $soal ? $this->user()->can('update', $soal) : $this->user()->can('create', BankSoal::class);
    }

    public function rules(): array
    {
        return [
            ...$this->aturanSoal(),
            'mapel_id' => ['required', 'integer', Rule::exists('mapels', 'id')],
            'topik' => ['nullable', 'string', 'max:255'],
            'tingkat_kesulitan' => ['required', Rule::in(BankSoal::TINGKAT_KESULITAN)],
            'tag' => ['nullable', 'array', 'max:10'],
            'tag.*' => ['string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->validasiSoal($validator);

        $validator->after(function (Validator $v) {
            $user = $this->user();
            if ($user->isGuru() && $this->filled('mapel_id')
                && ! $user->can('gunakanMapel', [Ujian::class, (int) $this->input('mapel_id')])) {
                $v->errors()->add('mapel_id', 'Anda tidak mengampu mata pelajaran ini.');
            }
        });
    }

    public function atribut(): array
    {
        return [
            ...$this->dataSoal(),
            'mapel_id' => $this->integer('mapel_id'),
            'topik' => $this->input('topik'),
            'tingkat_kesulitan' => $this->input('tingkat_kesulitan'),
            'tag' => array_values(array_filter($this->input('tag', []))) ?: null,
            'bobot_default' => $this->input('bobot'),
        ];
    }
}
