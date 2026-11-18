<?php

namespace App\Modules\Ujian\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi isi soal, dipakai bank soal dan soal ujian.
 */
trait AturanSoal
{
    protected function aturanSoal(): array
    {
        return [
            'tipe' => ['required', Rule::in(['pilihan_ganda', 'essay'])],
            'pertanyaan' => ['required', 'string', 'max:20000'],
            'opsi_a' => ['nullable', 'string', 'max:5000', 'required_if:tipe,pilihan_ganda'],
            'opsi_b' => ['nullable', 'string', 'max:5000', 'required_if:tipe,pilihan_ganda'],
            'opsi_c' => ['nullable', 'string', 'max:5000'],
            'opsi_d' => ['nullable', 'string', 'max:5000'],
            'opsi_e' => ['nullable', 'string', 'max:5000'],
            'kunci_jawaban' => ['nullable', 'required_if:tipe,pilihan_ganda', Rule::in(['A', 'B', 'C', 'D', 'E'])],
            'bobot' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('kunci_jawaban')) {
            $this->merge(['kunci_jawaban' => strtoupper((string) $this->input('kunci_jawaban'))]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($this->input('tipe') !== 'pilihan_ganda' || ! $this->filled('kunci_jawaban')) {
                return;
            }
            $kolom = 'opsi_'.strtolower((string) $this->input('kunci_jawaban'));
            if (blank($this->input($kolom))) {
                $v->errors()->add('kunci_jawaban', 'Kunci jawaban harus menunjuk opsi yang terisi.');
            }
        });
    }

    /**
     * Data soal siap simpan. Essay tidak menyimpan opsi/kunci.
     */
    protected function dataSoal(): array
    {
        $data = $this->safe()->only(['tipe', 'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'kunci_jawaban']);

        if ($data['tipe'] === 'essay') {
            $data = array_merge($data, array_fill_keys(['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'kunci_jawaban'], null));
        }

        return $data;
    }
}
