<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiPkl extends Model
{
    protected $fillable = [
        'penempatan_pkl_id', 'tanggal',
        'jam_masuk', 'jam_pulang',
        'latitude', 'longitude',
        'foto_masuk', 'foto_pulang', 'keterangan',
    ];

    protected $casts = [
        'tanggal'   => 'date',
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function penempatan() { return $this->belongsTo(PenempatanPkl::class, 'penempatan_pkl_id'); }

    public function getSudahPulangAttribute(): bool
    {
        return $this->jam_pulang !== null;
    }
}
