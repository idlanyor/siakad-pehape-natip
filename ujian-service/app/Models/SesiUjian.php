<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesiUjian extends Model
{
    use HasFactory;

    protected $table = 'sesi_ujian';

    protected $fillable = [
        'ujian_id',
        'siswa_id',
        'siswa_nama',
        'siswa_nis',
        'kelas_id',
        'status',
        'is_resmi',
        'fase',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_detik',
        'nilai_otomatis',
        'nilai_manual',
        'nilai_akhir',
        'nilai',
        'predikat',
        'tab_switch_count',
        'violation_count',
        'last_activity',
    ];

    protected $casts = [
        'is_resmi' => 'boolean',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'nilai_otomatis' => 'decimal:2',
        'nilai_manual' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'nilai' => 'decimal:2',
        'last_activity' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function setViolationCountAttribute($value): void
    {
        $this->attributes['tab_switch_count'] = $value;
        $this->attributes['violation_count'] = $value;
    }

    public function getViolationCountAttribute()
    {
        return $this->attributes['violation_count'] ?? $this->attributes['tab_switch_count'] ?? 0;
    }

    public function setNilaiAttribute($value): void
    {
        $this->attributes['nilai_akhir'] = $value;
        $this->attributes['nilai'] = $value;
    }

    public function getNilaiAttribute()
    {
        return $this->attributes['nilai'] ?? $this->attributes['nilai_akhir'] ?? null;
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = $value;
    }

    /**
     * Relasi ke ujian
     */
    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    /**
     * Relasi ke jawaban siswa
     */
    public function jawabans(): HasMany
    {
        return $this->hasMany(JawabanSiswa::class, 'sesi_ujian_id');
    }

    /**
     * Relasi ke proktor events
     */
    public function proktorEvents(): HasMany
    {
        return $this->hasMany(ProktorEvent::class, 'sesi_ujian_id');
    }

    /**
     * Scope untuk sesi dengan status tertentu
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk sesi resmi
     */
    public function scopeResmi($query)
    {
        return $query->where('is_resmi', true);
    }

    /**
     * Scope untuk sesi try out
     */
    public function scopeTryOut($query)
    {
        return $query->where('is_resmi', false);
    }

    /**
     * Scope untuk siswa tertentu
     */
    public function scopeSiswa($query, int $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }
}
