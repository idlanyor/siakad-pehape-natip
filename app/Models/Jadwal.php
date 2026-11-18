<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $fillable = [
        'mapel_id', 'kelas_id', 'guru_id', 'semester_id',
        'hari', 'jam_mulai', 'jam_selesai', 'ruangan', 'link_zoom',
    ];

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }

    public function materis()
    {
        return $this->hasMany(Materi::class);
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class);
    }

    public const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    /**
     * Urutkan Senin–Sabtu. Memakai CASE (portabel MySQL/SQLite/PostgreSQL),
     * bukan FIELD() yang khusus MySQL.
     */
    public function scopeUrutHari($query)
    {
        $case = 'CASE hari '.collect(self::HARI)->map(fn ($h, $i) => "WHEN '{$h}' THEN ".($i + 1))->implode(' ').' ELSE 99 END';

        return $query->orderByRaw($case);
    }

    public function getWaktuLengkapAttribute(): string
    {
        return "{$this->jam_mulai} - {$this->jam_selesai}";
    }
}
