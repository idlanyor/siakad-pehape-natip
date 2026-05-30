<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsensiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'jadwal_id'  => $this->jadwal_id,
            'siswa_id'   => $this->siswa_id,
            'tanggal'    => $this->tanggal?->toDateString(),
            'status'     => $this->status,
            'keterangan' => $this->keterangan,
            'mapel' => $this->whenLoaded('jadwal', fn() => [
                'nama' => $this->jadwal->mapel->nama_mapel ?? null,
            ]),
        ];
    }
}
