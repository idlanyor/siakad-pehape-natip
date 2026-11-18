<?php

namespace App\Modules\Ujian\Models;

use App\Models\User;
use Database\Factories\Ujian\FasePeriodeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class FasePeriode extends Model
{
    use HasFactory;

    public const TIPE = ['input_soal', 'try_out', 'pelaksanaan', 'remedial', 'rekapitulasi'];

    public const PAKSA_BUKA = 'paksa_buka';

    public const PAKSA_TUTUP = 'paksa_tutup';

    protected $table = 'fase_periode';

    protected $fillable = [
        'periode_id',
        'tipe',
        'urutan',
        'tgl_mulai',
        'tgl_selesai',
        'override_status',
        'override_by',
        'override_at',
    ];

    protected $casts = [
        'tgl_mulai' => 'datetime',
        'tgl_selesai' => 'datetime',
        'override_at' => 'datetime',
    ];

    protected static function newFactory(): FasePeriodeFactory
    {
        return FasePeriodeFactory::new();
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeUjian::class, 'periode_id');
    }

    public function overrideOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_by');
    }

    /**
     * Aktif bila dipaksa buka, atau (tanpa paksa tutup) sekarang berada di
     * rentang tanggal fase.
     */
    public function isAktif(?Carbon $waktu = null): bool
    {
        return match ($this->override_status) {
            self::PAKSA_BUKA => true,
            self::PAKSA_TUTUP => false,
            default => ($waktu ?? now())->between($this->tgl_mulai, $this->tgl_selesai),
        };
    }

    public function paksaBuka(User $oleh): void
    {
        $this->setOverride(self::PAKSA_BUKA, $oleh);
    }

    public function paksaTutup(User $oleh): void
    {
        $this->setOverride(self::PAKSA_TUTUP, $oleh);
    }

    public function resetOverride(): void
    {
        $this->update(['override_status' => null, 'override_by' => null, 'override_at' => null]);
    }

    public function hasOverride(): bool
    {
        return $this->override_status !== null;
    }

    public function getTipeLabelAttribute(): string
    {
        return match ($this->tipe) {
            'input_soal' => 'Input Soal',
            'try_out' => 'Try Out',
            'pelaksanaan' => 'Pelaksanaan',
            'remedial' => 'Remedial',
            'rekapitulasi' => 'Rekapitulasi',
            default => (string) $this->tipe,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->override_status) {
            self::PAKSA_BUKA => 'Dipaksa Buka',
            self::PAKSA_TUTUP => 'Dipaksa Tutup',
            default => $this->isAktif() ? 'Aktif' : 'Tidak Aktif',
        };
    }

    /**
     * Fase yang sedang aktif (dipaksa buka, atau sesuai tanggal tanpa paksa tutup).
     *
     * Catatan: versi ujian-service memakai `override_status != 'paksa_tutup'`,
     * yang di SQL tidak pernah cocok dengan baris ber-override NULL.
     */
    public function scopeAktif(Builder $query, ?Carbon $waktu = null): Builder
    {
        $waktu ??= now();

        return $query->where(function (Builder $q) use ($waktu) {
            $q->where('override_status', self::PAKSA_BUKA)
                ->orWhere(function (Builder $q) use ($waktu) {
                    $q->whereNull('override_status')
                        ->where('tgl_mulai', '<=', $waktu)
                        ->where('tgl_selesai', '>=', $waktu);
                });
        });
    }

    public function scopeTipe(Builder $query, string $tipe): Builder
    {
        return $query->where('tipe', $tipe);
    }

    private function setOverride(string $status, User $oleh): void
    {
        $this->update([
            'override_status' => $status,
            'override_by' => $oleh->id,
            'override_at' => now(),
        ]);
    }
}
