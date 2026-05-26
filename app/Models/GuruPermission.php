<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruPermission extends Model
{
    protected $fillable = [
        'guru_id',
        'menu_jadwal',
        'menu_mapel',
        'menu_absensi',
        'menu_nilai',
        'menu_pkl',
        'menu_ekskul',
    ];

    protected $casts = [
        'menu_jadwal'  => 'boolean',
        'menu_mapel'   => 'boolean',
        'menu_absensi' => 'boolean',
        'menu_nilai'   => 'boolean',
        'menu_pkl'     => 'boolean',
        'menu_ekskul'  => 'boolean',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
