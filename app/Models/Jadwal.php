<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $fillable = [
        'matkul_id', 'kelas_id', 'guru_id', 'semester_id',
        'hari', 'jam_mulai', 'jam_selesai', 'ruangan', 'link_zoom',
    ];

    public function matkul()   { return $this->belongsTo(Matkul::class); }
    public function kelas()    { return $this->belongsTo(Kelas::class); }
    public function guru()     { return $this->belongsTo(Guru::class); }
    public function semester() { return $this->belongsTo(Semester::class); }

    public function getWaktuLengkapAttribute(): string
    {
        return "{$this->jam_mulai} - {$this->jam_selesai}";
    }
}
