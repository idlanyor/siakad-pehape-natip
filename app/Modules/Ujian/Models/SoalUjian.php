<?php

namespace App\Modules\Ujian\Models;

use App\Modules\Ujian\Models\Concerns\PunyaOpsiJawaban;
use Database\Factories\Ujian\SoalUjianFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Soal yang terpasang pada satu ujian. Isinya disalin dari bank soal saat
 * dipasang, sehingga mengedit bank soal tidak mengubah ujian yang sudah ada.
 */
class SoalUjian extends Model
{
    use HasFactory, PunyaOpsiJawaban;

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
        'nomor' => 'integer',
        'bobot_nilai' => 'decimal:2',
    ];

    protected static function newFactory(): SoalUjianFactory
    {
        return SoalUjianFactory::new();
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class, 'bank_soal_id');
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(JawabanSiswa::class, 'soal_ujian_id');
    }

    /**
     * Salin isi soal dari bank soal (dipakai saat menyusun soal ujian).
     */
    public static function atributDariBankSoal(BankSoal $bankSoal): array
    {
        return [
            'bank_soal_id' => $bankSoal->id,
            'tipe' => $bankSoal->tipe,
            'pertanyaan' => $bankSoal->pertanyaan,
            'opsi_a' => $bankSoal->opsi_a,
            'opsi_b' => $bankSoal->opsi_b,
            'opsi_c' => $bankSoal->opsi_c,
            'opsi_d' => $bankSoal->opsi_d,
            'opsi_e' => $bankSoal->opsi_e,
            'kunci_jawaban' => $bankSoal->kunci_jawaban,
            'bobot_nilai' => $bankSoal->bobot_default,
        ];
    }
}
