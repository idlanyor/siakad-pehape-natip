<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MapelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $mapel = $this->route('mapel');

        return [
            'kode'       => ['required', 'string', 'max:20', Rule::unique('mapels', 'kode')->ignore($mapel?->id)],
            'nama_mapel' => 'required|string|max:100',
            'jurusan_id' => 'nullable|exists:jurusans,id',
            'guru_ids'   => 'nullable|array',
            'guru_ids.*' => 'exists:gurus,id',
        ];
    }
}
