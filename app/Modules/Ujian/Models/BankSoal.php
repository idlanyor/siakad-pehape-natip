<?php

namespace App\Modules\Ujian\Models;

use App\Models\Mapel;
use App\Models\User;
use App\Modules\Ujian\Models\Concerns\PunyaOpsiJawaban;
use Database\Factories\Ujian\BankSoalFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankSoal extends Model
{
    use HasFactory, PunyaOpsiJawaban;

    public const TINGKAT_KESULITAN = ['mudah', 'sedang', 'sulit'];

    protected $table = 'bank_soal';

    protected $fillable = [
        'mapel_id',
        'created_by',
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
    ];

    protected $casts = [
        'tag' => 'array',
        'bobot_default' => 'decimal:2',
    ];

    protected static function newFactory(): BankSoalFactory
    {
        return BankSoalFactory::new();
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function soalUjian(): HasMany
    {
        return $this->hasMany(SoalUjian::class, 'bank_soal_id');
    }

    public function scopeMapel(Builder $query, int $mapelId): Builder
    {
        return $query->where('mapel_id', $mapelId);
    }

    public function scopeTingkatKesulitan(Builder $query, string $tingkat): Builder
    {
        return $query->where('tingkat_kesulitan', $tingkat);
    }

    public function scopeTipe(Builder $query, string $tipe): Builder
    {
        return $query->where('tipe', $tipe);
    }

    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tag', $tag);
    }
}
