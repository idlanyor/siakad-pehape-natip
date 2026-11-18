<?php

namespace App\Modules\Ujian\Http\Requests;

use App\Modules\Ujian\Models\Ujian;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UjianRequest extends FormRequest
{
    /** Otorisasi sebelum validasi, supaya pengguna tanpa akses mendapat 403. */
    public function authorize(): bool
    {
        $ujian = $this->route('ujian');

        return $ujian ? $this->user()->can('update', $ujian) : $this->user()->can('create', Ujian::class);
    }

    public function rules(): array
    {
        return [
            'periode_id' => ['required', 'integer', Rule::exists('periode_ujian', 'id')->whereNot('status', 'selesai')],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'tipe' => ['required', Rule::in(Ujian::TIPE)],
            'mapel_id' => ['required', 'integer', Rule::exists('mapels', 'id')],
            'kelas_id' => ['required', 'integer', Rule::exists('kelas', 'id')],
            // Guru pemilik hanya dipilih admin; guru otomatis menjadi pemilik.
            'guru_id' => ['nullable', 'integer', Rule::exists('gurus', 'id')],
            'waktu_mulai' => ['required', 'date'],
            'waktu_selesai' => ['required', 'date', 'after:waktu_mulai'],
            'durasi_menit' => ['required', 'integer', 'min:1', 'max:600'],
            'passing_grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'acak_soal' => ['boolean'],
            'acak_opsi' => ['boolean'],
            'tampilkan_nilai' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'periode_id.exists' => 'Periode tidak ditemukan atau sudah selesai.',
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $jendela = Carbon::parse($this->input('waktu_mulai'))->diffInMinutes(Carbon::parse($this->input('waktu_selesai')));
            if ($this->integer('durasi_menit') > $jendela) {
                $v->errors()->add('durasi_menit', "Durasi melebihi rentang waktu ujian ({$jendela} menit).");
            }

            $user = $this->user();
            if ($user->isGuru() && ! $user->can('gunakanMapel', [Ujian::class, $this->integer('mapel_id')])) {
                $v->errors()->add('mapel_id', 'Anda tidak mengampu mata pelajaran ini.');
            }
        });
    }

    public function atribut(): array
    {
        $data = $this->safe()->except('guru_id');
        $data['acak_soal'] = $this->boolean('acak_soal');
        $data['acak_opsi'] = $this->boolean('acak_opsi');
        $data['tampilkan_nilai'] = $this->boolean('tampilkan_nilai');

        if ($this->user()->isAdmin()) {
            $data['guru_id'] = $this->input('guru_id');
        }

        return $data;
    }
}
