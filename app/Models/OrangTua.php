<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrangTua extends Model
{
    protected $table = 'orang_tuas';

    protected $fillable = ['user_id', 'nama', 'telepon', 'pekerjaan', 'alamat'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function siswas()
    {
        return $this->belongsToMany(Siswa::class, 'orang_tua_siswa')
            ->withPivot('hubungan')
            ->withTimestamps();
    }
}
