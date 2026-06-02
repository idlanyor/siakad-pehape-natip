<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Local siswa model for ujian-service
 * Data synced from SIAKAD main application
 * Siswa login using credentials validated against SIAKAD
 */
class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'siakad_id',
        'nis',
        'nisn',
        'nama_lengkap',
        'kelas_id',
        'kelas_nama',
        'tingkat',
        'username',
        'email',
        'status',
        'synced_at'
    ];

    protected $casts = [
        'synced_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get sesi ujian for this siswa
     */
    public function sesiUjians()
    {
        return $this->hasMany(SesiUjian::class, 'siswa_id');
    }

    /**
     * Check if siswa is active
     */
    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Get full name with NIS
     */
    public function getFullIdentityAttribute(): string
    {
        return "{$this->nama_lengkap} ({$this->nis})";
    }

    /**
     * Scope for active siswa only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope by kelas
     */
    public function scopeByKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    /**
     * Scope by tingkat
     */
    public function scopeByTingkat($query, $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }
}
