<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuruResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'nama'         => $this->nama,
            'nip'          => $this->nip,
            'bidang_studi' => $this->bidang_studi,
            'no_hp'        => $this->no_hp,
        ];
    }
}
