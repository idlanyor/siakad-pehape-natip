<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GelombangPpdb extends Model
{
    protected $fillable = [
        'nama', 'tahun_ajaran', 'tanggal_buka', 'tanggal_tutup',
        'kuota', 'keterangan', 'is_aktif',
    ];

    protected $casts = [
        'tanggal_buka'  => 'datetime',
        'tanggal_tutup' => 'datetime',
        'is_aktif'      => 'boolean',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    /** Gelombang yang sedang buka (tanggal sekarang dalam range) */
    public function scopeBuka($query)
    {
        return $query->aktif()
            ->where('tanggal_buka', '<=', now())
            ->where('tanggal_tutup', '>=', now());
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getIsBukaAttribute(): bool
    {
        return $this->is_aktif
            && now()->between($this->tanggal_buka, $this->tanggal_tutup);
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_aktif) return 'Nonaktif';
        if (now()->lt($this->tanggal_buka)) return 'Belum Dibuka';
        if (now()->gt($this->tanggal_tutup)) return 'Ditutup';
        return 'Dibuka';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_label) {
            'Dibuka'       => 'success',
            'Belum Dibuka' => 'warning',
            default        => 'secondary',
        };
    }

    public function pendaftars()
    {
        return $this->hasMany(PendaftarPpdb::class, 'gelombang_id');
    }

    public function getSisaKuotaAttribute(): ?int
    {
        if ($this->kuota === 0) return null; // tidak terbatas
        // Filter by gelombang_id (FK baru) untuk presisi per gelombang
        $terpakai = PendaftarPpdb::where('gelombang_id', $this->id)
            ->whereIn('status', ['verifikasi', 'diterima'])
            ->count();
        return max(0, $this->kuota - $terpakai);
    }
}
