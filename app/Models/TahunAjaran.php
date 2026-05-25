<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $fillable = ['nama', 'aktif'];

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }

    public static function aktif()
    {
        return static::where('aktif', true)->first();
    }
}
