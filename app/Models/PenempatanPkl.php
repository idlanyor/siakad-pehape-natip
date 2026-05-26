<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenempatanPkl extends Model
{
    protected $fillable = [
        'siswa_id', 'tempat_pkl_id', 'guru_pembimbing_id', 'semester_id',
        'periode_mulai', 'periode_selesai', 'status',
    ];

    protected $casts = [
        'periode_mulai'   => 'date',
        'periode_selesai' => 'date',
    ];

    const STATUS_LIST = ['aktif', 'selesai'];

    public function siswa()          { return $this->belongsTo(Siswa::class); }
    public function tempatPkl()      { return $this->belongsTo(TempatPkl::class, 'tempat_pkl_id'); }
    public function guruPembimbing() { return $this->belongsTo(Guru::class, 'guru_pembimbing_id'); }
    public function semester()       { return $this->belongsTo(Semester::class); }
    public function jurnals()        { return $this->hasMany(JurnalPkl::class, 'penempatan_pkl_id'); }
    public function absensis()       { return $this->hasMany(AbsensiPkl::class, 'penempatan_pkl_id'); }
    public function nilai()          { return $this->hasOne(NilaiPkl::class, 'penempatan_pkl_id'); }
}
