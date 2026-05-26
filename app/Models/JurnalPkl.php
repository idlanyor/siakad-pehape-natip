<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalPkl extends Model
{
    protected $fillable = ['penempatan_pkl_id', 'tanggal', 'kegiatan', 'foto', 'keterangan'];

    protected $casts = ['tanggal' => 'date'];

    public function penempatan() { return $this->belongsTo(PenempatanPkl::class, 'penempatan_pkl_id'); }
}
