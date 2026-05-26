<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    protected $fillable = ['kode', 'nama_mapel', 'dosen_pengampu'];

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }
}
