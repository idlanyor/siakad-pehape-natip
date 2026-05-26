<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggotaEkskul extends Model
{
    protected $fillable = ['ekskul_id', 'siswa_id', 'tahun_ajaran_id'];

    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
