<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FasePeriode extends Model
{
    use HasFactory;

    protected $table = 'fase_periode';

    protected $fillable = [
        'periode_id',
        'tipe',
        'urutan',
        'tgl_mulai',
        'tgl_selesai',
        'override_status',
        'override_oleh',
        'override_at',
    ];

    protected $casts = [
        'tgl_mulai' => 'datetime',
        'tgl_selesai' => 'datetime',
        'override_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke periode ujian
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeUjian::class, 'periode_id');
    }

    /**
     * Cek apakah fase sedang aktif berdasarkan tanggal
     */
    public function isAktifByTanggal(): bool
    {
        // Jika ada override paksa tutup, tidak aktif
        if ($this->override_status === 'paksa_tutup') {
            return false;
        }

        // Jika ada override paksa buka, aktif
        if ($this->override_status === 'paksa_buka') {
            return true;
        }

        // Cek berdasarkan tanggal
        $now = Carbon::now();
        return $now->between($this->tgl_mulai, $this->tgl_selesai);
    }

    /**
     * Override fase menjadi paksa buka
     */
    public function paksaBuka(string $oleh): void
    {
        $this->override_status = 'paksa_buka';
        $this->override_oleh = $oleh;
        $this->override_at = Carbon::now();
        $this->save();

        // Refresh fase aktif di periode
        $this->periode->refreshFaseAktif();
    }

    /**
     * Override fase menjadi paksa tutup
     */
    public function paksaTutup(string $oleh): void
    {
        $this->override_status = 'paksa_tutup';
        $this->override_oleh = $oleh;
        $this->override_at = Carbon::now();
        $this->save();

        // Refresh fase aktif di periode
        $this->periode->refreshFaseAktif();
    }

    /**
     * Reset override ke otomatis
     */
    public function resetOverride(): void
    {
        $this->override_status = null;
        $this->override_oleh = null;
        $this->override_at = null;
        $this->save();

        // Refresh fase aktif di periode
        $this->periode->refreshFaseAktif();
    }

    /**
     * Cek apakah fase memiliki override
     */
    public function hasOverride(): bool
    {
        return !is_null($this->override_status);
    }

    /**
     * Get label tipe fase yang human-readable
     */
    public function getTipeLabel(): string
    {
        return match($this->tipe) {
            'input_soal' => 'Input Soal',
            'try_out' => 'Try Out',
            'pelaksanaan' => 'Pelaksanaan',
            'remedial' => 'Remedial',
            'rekapitulasi' => 'Rekapitulasi',
            default => $this->tipe,
        };
    }

    /**
     * Get status fase (aktif/tidak aktif/override)
     */
    public function getStatusLabel(): string
    {
        if ($this->override_status === 'paksa_buka') {
            return 'Dipaksa Buka';
        }

        if ($this->override_status === 'paksa_tutup') {
            return 'Dipaksa Tutup';
        }

        return $this->isAktifByTanggal() ? 'Aktif' : 'Tidak Aktif';
    }

    /**
     * Scope untuk fase dengan tipe tertentu
     */
    public function scopeTipe($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    /**
     * Scope untuk fase yang sedang aktif
     */
    public function scopeAktif($query)
    {
        $now = Carbon::now();
        return $query->where(function($q) use ($now) {
            $q->where('override_status', 'paksa_buka')
              ->orWhere(function($q2) use ($now) {
                  $q2->whereNull('override_status')
                     ->where('tgl_mulai', '<=', $now)
                     ->where('tgl_selesai', '>=', $now);
              });
        })->where('override_status', '!=', 'paksa_tutup');
    }

    /**
     * Scope untuk fase dengan override
     */
    public function scopeWithOverride($query)
    {
        return $query->whereNotNull('override_status');
    }
}