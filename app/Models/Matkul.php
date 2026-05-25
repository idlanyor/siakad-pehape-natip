<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matkul extends Model
{
    protected $fillable = ['kode', 'nama_matkul', 'dosen_pengampu'];

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }
}
