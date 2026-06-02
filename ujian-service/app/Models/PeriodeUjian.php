<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PeriodeUjian extends Model
{
    use HasFactory;

    protected $table = 'periode_ujian';

    protected $fillable = [
        'nama',
        'tahun_ajaran',
        'semester',
        'status',
        'fase_aktif',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function setSemesterAttribute($value): void
    {
        $this->attributes['semester'] = is_numeric($value)
            ? ((int) $value === 1 ? 'ganjil' : 'genap')
            : strtolower((string) $value);
    }

    /**
     * Relasi ke fase periode
     */
    public function fases(): HasMany
    {
        return $this->hasMany(FasePeriode::class, 'periode_id')->orderBy('urutan');
    }

    /**
     * Relasi ke ujian
     */
    public function ujians(): HasMany
    {
        return $this->hasMany(Ujian::class, 'periode_id');
    }

    /**
     * Hitung fase aktif berdasarkan tanggal saat ini
     * Mempertimbangkan override manual
     */
    public function hitungFaseAktif(): ?string
    {
        $now = Carbon::now();
        
        foreach ($this->fases as $fase) {
            // Jika ada override, gunakan override
            if ($fase->override_status === 'paksa_buka') {
                return $fase->tipe;
            }
            
            if ($fase->override_status === 'paksa_tutup') {
                continue;
            }
            
            // Cek apakah sekarang dalam rentang fase ini
            $mulai = Carbon::parse($fase->tgl_mulai);
            $selesai = Carbon::parse($fase->tgl_selesai);
            
            if ($now->between($mulai, $selesai)) {
                return $fase->tipe;
            }
        }
        
        return null;
    }

    /**
     * Update cache fase_aktif
     */
    public function refreshFaseAktif(): void
    {
        $this->fase_aktif = $this->hitungFaseAktif();
        $this->save();
    }

    /**
     * Cek apakah periode sedang aktif
     */
    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Cek apakah fase tertentu sedang aktif
     */
    public function isFaseAktif(string $tipe): bool
    {
        return $this->fase_aktif === $tipe;
    }

    /**
     * Scope untuk periode aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope untuk tahun ajaran tertentu
     */
    public function scopeTahunAjaran($query, string $tahunAjaran)
    {
        return $query->where('tahun_ajaran', $tahunAjaran);
    }

    /**
     * Scope untuk semester tertentu
     */
    public function scopeSemester($query, string $semester)
    {
        return $query->where('semester', $semester);
    }
}
