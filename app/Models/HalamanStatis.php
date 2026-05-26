<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HalamanStatis extends Model
{
    protected $fillable = ['slug', 'judul', 'konten', 'meta_desc', 'is_published', 'urutan'];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
