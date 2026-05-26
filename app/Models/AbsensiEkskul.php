<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiEkskul extends Model
{
    protected $fillable = ['ekskul_id', 'siswa_id', 'tanggal', 'status', 'keterangan', 'dicatat_oleh'];

    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
