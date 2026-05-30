<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_kelas'    => 'required|string|max:50',
            'jurusan_id'    => 'required|exists:jurusans,id',
            'tingkat'       => 'required|in:X,XI,XII',
            'wali_kelas_id' => 'nullable|exists:gurus,id',
        ];
    }
}
