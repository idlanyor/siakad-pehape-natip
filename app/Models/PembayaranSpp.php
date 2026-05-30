<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranSpp extends Model
{
    use \App\Traits\Auditable;

    protected $fillable = [
        'tagihan_spp_id', 'tanggal_bayar', 'nominal_bayar',
        'metode', 'bukti', 'catatan', 'dicatat_oleh',
        'status_konfirmasi', 'dikonfirmasi_oleh', 'dikonfirmasi_at',
        'midtrans_order_id', 'midtrans_transaction_id', 'payment_type',
    ];

    protected $casts = [
        'tanggal_bayar'   => 'date',
        'nominal_bayar'   => 'decimal:2',
        'dikonfirmasi_at' => 'datetime',
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

    public function pengkonfirmasi()
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh');
    }

    public function isPending(): bool
    {
        return $this->status_konfirmasi === 'pending';
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
