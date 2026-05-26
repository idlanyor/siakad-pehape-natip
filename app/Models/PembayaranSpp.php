<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranSpp extends Model
{
    protected $fillable = [
        'tagihan_spp_id', 'tanggal_bayar', 'nominal_bayar',
        'metode', 'bukti', 'catatan', 'dicatat_oleh',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'nominal_bayar' => 'decimal:2',
    ];

    const METODE_LIST = [
        'tunai'    => 'Tunai',
        'transfer' => 'Transfer Bank',
        'qris'     => 'QRIS',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function tagihan()
    {
        return $this->belongsTo(TagihanSpp::class, 'tagihan_spp_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getBuktiUrlAttribute(): ?string
    {
        return $this->bukti ? asset('storage/' . $this->bukti) : null;
    }

    public function getNominalRpAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal_bayar, 0, ',', '.');
    }

    public function getMetodeLabelAttribute(): string
    {
        return self::METODE_LIST[$this->metode] ?? $this->metode;
    }
}
