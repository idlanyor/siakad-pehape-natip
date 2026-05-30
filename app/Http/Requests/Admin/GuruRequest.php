<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $guru = $this->route('guru');
        $userId = $guru?->user_id;

        return [
            'name'         => 'required|string|max:100',
            'username'     => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($userId)],
            'email'        => ['nullable', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password'     => [$guru ? 'nullable' : 'required', 'string', 'min:6'],
            'nip'          => ['nullable', 'string', 'max:30', Rule::unique('gurus', 'nip')->ignore($guru?->id)],
            'bidang_studi' => 'nullable|string|max:100',
            'no_hp'        => 'nullable|string|max:20',
        ];
    }
}
