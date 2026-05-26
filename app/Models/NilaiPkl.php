<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiPkl extends Model
{
    protected $fillable = [
        'penempatan_pkl_id',
        'nilai_pembimbing', 'nilai_du_di', 'nilai_sikap',
        'catatan', 'dicatat_oleh',
    ];

    protected $casts = [
        'nilai_pembimbing' => 'float',
        'nilai_du_di'      => 'float',
        'nilai_sikap'      => 'float',
    ];

    public function penempatan() { return $this->belongsTo(PenempatanPkl::class, 'penempatan_pkl_id'); }
    public function pencatat()   { return $this->belongsTo(User::class, 'dicatat_oleh'); }

    public function getNilaiAkhirAttribute(): ?float
    {
        $vals = collect(['nilai_pembimbing', 'nilai_du_di', 'nilai_sikap'])
            ->map(fn($k) => $this->$k)
            ->filter(fn($v) => $v !== null);
        return $vals->isEmpty() ? null : round($vals->avg(), 2);
    }

    public function getPredikatAttribute(): string
    {
        $n = $this->nilai_akhir;
        if ($n === null) return '-';
        return match (true) {
            $n >= 90 => 'A',
            $n >= 80 => 'B',
            $n >= 70 => 'C',
            default  => 'D',
        };
    }

    public function getPredikatColorAttribute(): string
    {
        return match ($this->predikat) {
            'A' => 'success', 'B' => 'primary', 'C' => 'warning', 'D' => 'danger', default => 'secondary',
        };
    }
}
