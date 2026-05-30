<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NilaiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'jadwal_id'         => $this->jadwal_id,
            'siswa_id'          => $this->siswa_id,
            'tugas'             => $this->tugas,
            'pts'               => $this->pts,
            'pas'               => $this->pas,
            'praktik'           => $this->praktik,
            'proyek'            => $this->proyek,
            'portofolio'        => $this->portofolio,
            'rata_pengetahuan'  => $this->rata_pengetahuan,
            'rata_keterampilan' => $this->rata_keterampilan,
            'nilai_akhir'       => $this->nilai_akhir,
            'predikat'          => $this->predikat,
            'catatan'           => $this->catatan,
            'mapel' => $this->whenLoaded('jadwal', fn() => [
                'nama' => $this->jadwal->mapel->nama_mapel ?? null,
            ]),
        ];
    }
}
