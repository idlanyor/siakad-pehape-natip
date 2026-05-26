<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggaran extends Model
{
    protected $fillable = ['siswa_id', 'tanggal', 'jenis', 'deskripsi', 'poin', 'dicatat_oleh'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
