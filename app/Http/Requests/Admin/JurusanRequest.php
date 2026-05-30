<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JurusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $jurusan = $this->route('jurusan');

        return [
            'nama_jurusan' => ['required', 'string', 'max:100', Rule::unique('jurusans', 'nama_jurusan')->ignore($jurusan?->id)],
            'gambar'       => 'nullable|image|max:2048',
        ];
    }
}
