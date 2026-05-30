<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    protected $fillable = ['kode', 'nama_mapel', 'jurusan_id'];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function gurus()
    {
        return $this->belongsToMany(Guru::class, 'guru_mapel')->withTimestamps();
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    /**
     * Nama guru pengampu (gabungan dari pivot).
     */
    public function getGuruPengampuAttribute(): string
    {
        return $this->gurus->pluck('nama')->implode(', ') ?: '-';
    }
}
