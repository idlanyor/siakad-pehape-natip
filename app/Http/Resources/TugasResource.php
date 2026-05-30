<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TugasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'jadwal_id'         => $this->jadwal_id,
            'judul'             => $this->judul,
            'instruksi'         => $this->instruksi,
            'deadline'          => $this->deadline,
            'nilai_maksimal'    => $this->nilai_maksimal,
            'tipe_pengumpulan'  => $this->tipe_pengumpulan,
            'tipe_label'        => $this->tipe_label,
            'is_expired'        => $this->is_expired,
            'sisa_waktu'        => $this->sisa_waktu,
            'mapel' => $this->whenLoaded('jadwal', fn () => $this->jadwal->mapel->nama_mapel ?? null),
            // Status pengumpulan siswa, diisi controller saat relevan.
            'pengumpulan' => $this->when(
                $this->resource->relationLoaded('pengumpulanku') || isset($this->pengumpulanku),
                fn () => $this->pengumpulanku ? new PengumpulanTugasResource($this->pengumpulanku) : null
            ),
        ];
    }
}
