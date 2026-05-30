<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $siswa = $this->route('siswa');
        $userId = $siswa?->user_id;

        return [
            'name'          => 'required|string|max:100',
            'username'      => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($userId)],
            'email'         => ['nullable', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password'      => [$siswa ? 'nullable' : 'required', 'string', 'min:6'],
            'nisn'          => ['required', 'string', 'max:20', Rule::unique('siswas', 'nisn')->ignore($siswa?->id)],
            'jurusan_id'    => 'required|exists:jurusans,id',
            'kelas_id'      => 'nullable|exists:kelas,id',
            'tanggal_lahir' => 'required|date',
        ];
    }
}
