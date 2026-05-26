<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Galeri extends Model
{
    protected $fillable = ['nama', 'deskripsi', 'cover', 'tanggal', 'is_published'];

    protected $casts = [
        'tanggal'      => 'date',
        'is_published' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(GaleriItem::class)->orderBy('urutan');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover ? asset('storage/' . $this->cover) : null;
    }

    public function getJumlahFotoAttribute(): int
    {
        return $this->items()->count();
    }
}
