<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $fillable = ['tahun_ajaran_id', 'semester', 'aktif'];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public static function aktif()
    {
        return static::where('aktif', true)->first();
    }
}
