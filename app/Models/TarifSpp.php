<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifSpp extends Model
{
    protected $fillable = ['kelas_id', 'tahun_ajaran_id', 'nominal'];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Format nominal sebagai Rupiah.
     */
    public function getNominalRpAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }
}
