<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaleriItem extends Model
{
    protected $fillable = ['galeri_id', 'foto', 'keterangan', 'urutan'];

    public function galeri()
    {
        return $this->belongsTo(Galeri::class);
    }

    public function getFotoUrlAttribute(): string
    {
        return asset('storage/' . $this->foto);
    }
}
