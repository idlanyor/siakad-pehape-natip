<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JadwalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'hari'        => $this->hari,
            'jam_mulai'   => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'ruangan'     => $this->ruangan,
            'mapel'       => $this->whenLoaded('mapel', fn() => [
                'id'   => $this->mapel->id,
                'nama' => $this->mapel->nama_mapel,
            ]),
            'kelas' => $this->whenLoaded('kelas', fn() => [
                'id'         => $this->kelas->id,
                'nama_kelas' => $this->kelas->nama_kelas,
            ]),
            'guru' => $this->whenLoaded('guru', fn() => [
                'id'   => $this->guru->id,
                'nama' => $this->guru->nama,
            ]),
            'semester' => $this->whenLoaded('semester', fn() => [
                'id'       => $this->semester->id,
                'semester' => $this->semester->semester,
            ]),
        ];
    }
}
