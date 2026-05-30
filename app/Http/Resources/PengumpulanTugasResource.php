<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengumpulanTugasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'tugas_id'     => $this->tugas_id,
            'siswa_id'     => $this->siswa_id,
            'konten'       => $this->konten,
            'file_name'    => $this->file_name,
            'catatan'      => $this->catatan,
            'download_url' => $this->download_url,
            'nilai'        => $this->nilai,
            'nilai_persen' => $this->nilai_persen,
            'feedback'     => $this->feedback,
            'is_dinilai'   => $this->is_dinilai,
            'status_label' => $this->status_label,
            'dinilai_at'   => $this->dinilai_at,
            'created_at'   => $this->created_at,
            'siswa' => $this->whenLoaded('siswa', fn () => [
                'id'   => $this->siswa->id,
                'nama' => $this->siswa->nama,
                'nisn' => $this->siswa->nisn,
            ]),
        ];
    }
}
