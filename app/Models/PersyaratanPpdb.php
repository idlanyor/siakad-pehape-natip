<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersyaratanPpdb extends Model
{
    protected $fillable = ['judul', 'deskripsi', 'urutan', 'is_aktif'];

    protected $casts = ['is_aktif' => 'boolean'];

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true)->orderBy('urutan');
    }
}
