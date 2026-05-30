<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagihanSppResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'bulan'           => $this->bulan?->toDateString(),
            'bulan_label'     => $this->bulan_label,
            'nominal'         => (float) $this->nominal,
            'nominal_rp'      => $this->nominal_rp,
            'status'          => $this->status,
            'status_label'    => $this->status_label,
            'total_dibayar'   => $this->total_dibayar,
            'sisa_tagihan'    => $this->sisa_tagihan,
            'pembayarans'     => $this->whenLoaded('pembayarans', fn() =>
                $this->pembayarans->map(fn($p) => [
                    'id'             => $p->id,
                    'tanggal_bayar'  => $p->tanggal_bayar?->toDateString(),
                    'nominal_bayar'  => (float) $p->nominal_bayar,
                    'metode'         => $p->metode,
                    'metode_label'   => $p->metode_label,
                    'status_konfirmasi' => $p->status_konfirmasi,
                ])
            ),
        ];
    }
}
