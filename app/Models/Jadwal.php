<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $fillable = ['matkul_id', 'hari', 'waktu', 'link_zoom'];

    public function matkul()
    {
        return $this->belongsTo(Matkul::class);
    }
}
