<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ujian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ujian';

    protected $fillable = [
        'periode_id',
        'fase_id',
        'judul',
        'deskripsi',
        'mapel_id',
        'mapel_nama',
        'kelas_id',
        'kelas_nama',
        'guru_id',
        'guru_nama',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_menit',
        'jumlah_soal',
        'passing_grade',
        'acak_soal',
        'acak_opsi',
        'tampilkan_nilai',
        'status',
        'is_published',
        'periode_ujian_id',
        // Alias field lama (legacy) — dipetakan lewat mutator di bawah.
        'tanggal_mulai',
        'tanggal_selesai',
        'durasi',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'acak_soal' => 'boolean',
        'acak_opsi' => 'boolean',
        'tampilkan_nilai' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function setPeriodeUjianIdAttribute($value)
    {
        $this->attributes['periode_id'] = $value;
    }

    public function getPeriodeUjianIdAttribute()
    {
        return $this->attributes['periode_id'] ?? null;
    }

    /*
     |---------------------------------------------------------------------------
     | Alias field legacy
     |---------------------------------------------------------------------------
     | Kolom kanonik di tabel `ujian` adalah waktu_mulai, waktu_selesai, dan
     | durasi_menit. Request lama (SIAKAD) serta data factory masih memakai
     | tanggal_mulai, tanggal_selesai, dan durasi. Alias di sini memetakan
     | penulisan field lama ke kolom kanonik agar keduanya tetap kompatibel.
     */

    public function setTanggalMulaiAttribute($value)
    {
        $this->attributes['waktu_mulai'] = $value;
    }

    public function getTanggalMulaiAttribute()
    {
        return $this->attributes['waktu_mulai'] ?? null;
    }

    public function setTanggalSelesaiAttribute($value)
    {
        $this->attributes['waktu_selesai'] = $value;
    }

    public function getTanggalSelesaiAttribute()
    {
        return $this->attributes['waktu_selesai'] ?? null;
    }

    public function setDurasiAttribute($value)
    {
        $this->attributes['durasi_menit'] = $value;
    }

    public function getDurasiAttribute()
    {
        return $this->attributes['durasi_menit'] ?? null;
    }

    /**
     * Relasi ke periode
     */
    public function periode()
    {
        return $this->belongsTo(PeriodeUjian::class, 'periode_id');
    }

    /**
     * Relasi ke fase
     */
    public function fase()
    {
        return $this->belongsTo(FasePeriode::class, 'fase_id');
    }

    /**
     * Relasi ke soal
     */
    public function soal()
    {
        return $this->hasMany(SoalUjian::class, 'ujian_id');
    }

    /**
     * Relasi ke sesi ujian
     */
    public function sesi()
    {
        return $this->hasMany(SesiUjian::class, 'ujian_id');
    }

    /** Alias jamak agar konsisten dgn pemanggilan store() & view */
    public function soals()
    {
        return $this->hasMany(SoalUjian::class, 'ujian_id');
    }

    public function sesis()
    {
        return $this->hasMany(SesiUjian::class, 'ujian_id');
    }

    public function sesiUjians()
    {
        return $this->hasMany(SesiUjian::class, 'ujian_id');
    }

    public function soalUjians()
    {
        return $this->hasMany(SoalUjian::class, 'ujian_id');
    }

    public function getStatusColorAttribute(): string
    {
        return ($this->status === 'published' || $this->is_published)
            ? 'card-success' : 'card-secondary';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match (true) {
            $this->status === 'published' || $this->is_published => 'success',
            $this->status === 'selesai' => 'dark',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match (true) {
            $this->status === 'published' || $this->is_published => 'Tersedia',
            $this->status === 'selesai' => 'Selesai',
            default => 'Belum dibuka',
        };
    }
}
