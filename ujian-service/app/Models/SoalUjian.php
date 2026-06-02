<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoalUjian extends Model
{
    use HasFactory;

    protected $table = 'soal_ujian';

    protected $fillable = [
        'ujian_id',
        'bank_soal_id',
        'nomor',
        'tipe',
        'pertanyaan',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'opsi_e',
        'kunci_jawaban',
        'bobot_nilai',
    ];

    protected $casts = [
        'bobot_nilai' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function setNomorUrutAttribute($value): void
    {
        $this->attributes['nomor'] = $value;
    }

    public function setBobotAttribute($value): void
    {
        $this->attributes['bobot_nilai'] = $value;
    }

    public function setJawabanBenarAttribute($value): void
    {
        $this->attributes['kunci_jawaban'] = $value;
    }

    public function getNomorUrutAttribute()
    {
        return $this->attributes['nomor'] ?? null;
    }

    public function getBobotAttribute()
    {
        return $this->attributes['bobot_nilai'] ?? null;
    }

    public function getJawabanBenarAttribute()
    {
        return $this->attributes['kunci_jawaban'] ?? null;
    }

    /**
     * Relasi ke ujian
     */
    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    /**
     * Relasi ke bank soal (nullable)
     */
    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class, 'bank_soal_id');
    }
}
