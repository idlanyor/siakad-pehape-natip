<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekskul extends Model
{
    protected $fillable = ['nama', 'pembina_id', 'deskripsi'];

    public function pembina()
    {
        return $this->belongsTo(Guru::class, 'pembina_id');
    }

    public function anggotas()
    {
        return $this->hasMany(AnggotaEkskul::class);
    }

    public function absensis()
    {
        return $this->hasMany(AbsensiEkskul::class);
    }
}
