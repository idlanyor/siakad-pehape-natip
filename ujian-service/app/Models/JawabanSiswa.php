<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanSiswa extends Model
{
    use HasFactory;

    protected $table = 'jawaban_siswa';

    protected $fillable = [
        'sesi_ujian_id',
        'soal_ujian_id',
        'soal_id',
        'jawaban_pilihan',
        'jawaban_text',
        'jawaban',
        'nomor_urut',
        'is_ragu',
        'nilai',
        'is_benar',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'is_benar' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function setSoalIdAttribute($value): void
    {
        $this->attributes['soal_ujian_id'] = $value;
        $this->attributes['soal_id'] = $value;
    }

    public function setSoalUjianIdAttribute($value): void
    {
        $this->attributes['soal_ujian_id'] = $value;
        $this->attributes['soal_id'] = $value;
    }

    public function getSoalIdAttribute()
    {
        return $this->attributes['soal_id'] ?? $this->attributes['soal_ujian_id'] ?? null;
    }

    public function setJawabanAttribute($value): void
    {
        $this->attributes['jawaban_pilihan'] = is_string($value) && strlen($value) === 1
            ? strtoupper($value)
            : null;
        $this->attributes['jawaban_text'] = $value;
        $this->attributes['jawaban'] = $value;
    }

    public function getJawabanAttribute()
    {
        return $this->attributes['jawaban'] ?? $this->attributes['jawaban_text'] ?? $this->attributes['jawaban_pilihan'] ?? null;
    }

    public function setNomorUrutAttribute($value): void
    {
        // The current schema derives order from the related soal snapshot.
    }

    public function getNomorUrutAttribute()
    {
        return $this->soal?->nomor ?? $this->soalUjian?->nomor ?? null;
    }

    public function setIsRaguAttribute($value): void
    {
        $this->attributes['is_ragu'] = (bool) $value;
    }

    public function getIsRaguAttribute(): bool
    {
        return (bool) ($this->attributes['is_ragu'] ?? false);
    }

    /**
     * Relasi ke sesi ujian
     */
    public function sesiUjian(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class, 'sesi_ujian_id');
    }

    /**
     * Relasi ke soal ujian
     */
    public function soalUjian(): BelongsTo
    {
        return $this->belongsTo(SoalUjian::class, 'soal_ujian_id');
    }

    public function soal(): BelongsTo
    {
        return $this->soalUjian();
    }

    /**
     * Scope untuk jawaban benar
     */
    public function scopeBenar($query)
    {
        return $query->where('is_benar', true);
    }

    /**
     * Scope untuk jawaban salah
     */
    public function scopeSalah($query)
    {
        return $query->where('is_benar', false);
    }

    /**
     * Scope untuk jawaban pilihan ganda
     */
    public function scopePilihanGanda($query)
    {
        return $query->whereNotNull('jawaban_pilihan');
    }

    /**
     * Scope untuk jawaban essay
     */
    public function scopeEssay($query)
    {
        return $query->whereNotNull('jawaban_text');
    }
}
