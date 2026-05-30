<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiswaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'nama'          => $this->nama,
            'nisn'          => $this->nisn,
            'tanggal_lahir' => $this->tanggal_lahir,
            'kelas'         => $this->whenLoaded('kelas', fn() => [
                'id'         => $this->kelas->id,
                'nama_kelas' => $this->kelas->nama_kelas,
                'tingkat'    => $this->kelas->tingkat,
            ]),
            'jurusan' => $this->whenLoaded('jurusan', fn() => [
                'id'   => $this->jurusan->id,
                'nama' => $this->jurusan->nama_jurusan,
            ]),
        ];
    }
}
