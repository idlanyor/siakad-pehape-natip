<?php

namespace App\Modules\Ujian\Models;

use App\Models\Semester;
use Database\Factories\Ujian\PeriodeUjianFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Periode ujian (mis. "UAS Ganjil 2026/2027") yang terbagi menjadi beberapa fase.
 *
 * @property-read string|null $fase_aktif Tipe fase yang sedang berlaku.
 */
class PeriodeUjian extends Model
{
    use HasFactory;

    public const STATUS = ['draft', 'aktif', 'selesai'];

    protected $table = 'periode_ujian';

    protected $fillable = ['nama', 'semester_id', 'status'];

    protected static function newFactory(): PeriodeUjianFactory
    {
        return PeriodeUjianFactory::new();
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function fases(): HasMany
    {
        return $this->hasMany(FasePeriode::class, 'periode_id')->orderBy('urutan');
    }

    public function ujians(): HasMany
    {
        return $this->hasMany(Ujian::class, 'periode_id');
    }

    /**
     * Fase yang sedang berlaku, dihitung saat dibaca (tidak di-cache di kolom,
     * supaya tidak basi ketika tanggal bergeser).
     *
     * Prioritas: fase yang dipaksa buka, lalu fase yang aktif berdasarkan
     * tanggal (urut sesuai `urutan`). Fase yang dipaksa tutup dilewati.
     */
    public function getFaseAktifAttribute(): ?string
    {
        return $this->faseAktif()?->tipe;
    }

    public function faseAktif(): ?FasePeriode
    {
        $fases = $this->fases;

        return $fases->firstWhere('override_status', FasePeriode::PAKSA_BUKA)
            ?? $fases->first(fn (FasePeriode $fase) => $fase->isAktif());
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function isFaseAktif(string $tipe): bool
    {
        return $this->fase_aktif === $tipe;
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }
}
