<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MateriResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'jadwal_id'    => $this->jadwal_id,
            'judul'        => $this->judul,
            'deskripsi'    => $this->deskripsi,
            'tipe'         => $this->tipe,
            'tipe_label'   => $this->tipe_label,
            'konten'       => $this->konten,
            'file_name'    => $this->file_name,
            'download_url' => $this->download_url,
            'is_published' => $this->is_published,
            'created_at'   => $this->created_at,
            'mapel' => $this->whenLoaded('jadwal', fn () => $this->jadwal->mapel->nama_mapel ?? null),
            'guru'  => $this->whenLoaded('guru', fn () => $this->guru->nama ?? null),
        ];
    }
}
