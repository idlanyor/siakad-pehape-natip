<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    protected $fillable = ['nama_jurusan', 'deskripsi_publik', 'gambar', 'icon', 'is_aktif_ppdb'];

    protected $casts = ['is_aktif_ppdb' => 'boolean'];

    public function siswas()
    {
        return $this->hasMany(Siswa::class);
    }
}
