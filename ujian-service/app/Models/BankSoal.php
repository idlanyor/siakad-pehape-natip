<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankSoal extends Model
{
    use HasFactory;

    protected $table = 'bank_soal';

    protected $fillable = [
        'mapel_id',
        'mapel_nama',
        'topik',
        'tingkat_kesulitan',
        'tag',
        'tipe',
        'pertanyaan',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'opsi_e',
        'kunci_jawaban',
        'bobot_default',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tag' => 'array',
        'bobot_default' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke soal ujian yang menggunakan bank soal ini
     */
    public function soalUjians(): HasMany
    {
        return $this->hasMany(SoalUjian::class, 'bank_soal_id');
    }

    /**
     * Scope untuk mapel tertentu
     */
    public function scopeMapel($query, int $mapelId)
    {
        return $query->where('mapel_id', $mapelId);
    }

    /**
     * Scope untuk tingkat kesulitan tertentu
     */
    public function scopeTingkatKesulitan($query, string $tingkat)
    {
        return $query->where('tingkat_kesulitan', $tingkat);
    }

    /**
     * Scope untuk tipe soal tertentu
     */
    public function scopeTipe($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    /**
     * Scope untuk pencarian berdasarkan tag
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tag', $tag);
    }
}