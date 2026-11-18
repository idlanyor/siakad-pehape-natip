<?php

namespace App\Modules\Ujian\Models;

use Database\Factories\Ujian\JawabanSiswaFactory;
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
        'jawaban_pilihan',
        'jawaban_text',
        'is_ragu',
        'is_benar',
        'nilai',
    ];

    protected $casts = [
        'is_ragu' => 'boolean',
        'is_benar' => 'boolean',
        'nilai' => 'decimal:2',
    ];

    protected static function newFactory(): JawabanSiswaFactory
    {
        return JawabanSiswaFactory::new();
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class, 'sesi_ujian_id');
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(SoalUjian::class, 'soal_ujian_id');
    }

    /**
     * Pilihan ganda disimpan dalam huruf kapital (A–E).
     */
    public function setJawabanPilihanAttribute(?string $value): void
    {
        $this->attributes['jawaban_pilihan'] = $value === null || $value === '' ? null : strtoupper($value);
    }
}
