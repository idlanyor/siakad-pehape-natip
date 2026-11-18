<?php

namespace App\Modules\Ujian\Models;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use Database\Factories\Ujian\UjianFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ujian extends Model
{
    use HasFactory, SoftDeletes;

    public const TIPE = ['quiz', 'uts', 'uas', 'remidi'];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_SELESAI = 'selesai';

    protected $table = 'ujian';

    protected $fillable = [
        'periode_id',
        'judul',
        'deskripsi',
        'tipe',
        'jadwal_id',
        'mapel_id',
        'kelas_id',
        'guru_id',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_menit',
        'passing_grade',
        'acak_soal',
        'acak_opsi',
        'tampilkan_nilai',
        'status',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'durasi_menit' => 'integer',
        'passing_grade' => 'decimal:2',
        'acak_soal' => 'boolean',
        'acak_opsi' => 'boolean',
        'tampilkan_nilai' => 'boolean',
    ];

    protected static function newFactory(): UjianFactory
    {
        return UjianFactory::new();
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeUjian::class, 'periode_id');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function soal(): HasMany
    {
        return $this->hasMany(SoalUjian::class, 'ujian_id')->orderBy('nomor');
    }

    public function sesi(): HasMany
    {
        return $this->hasMany(SesiUjian::class, 'ujian_id');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Ujian bisa dikerjakan: sudah dipublikasikan dan sekarang di dalam jendela waktunya.
     */
    public function isDibuka(): bool
    {
        return $this->isPublished() && now()->between($this->waktu_mulai, $this->waktu_selesai);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'Tersedia',
            self::STATUS_SELESAI => 'Selesai',
            default => 'Belum dibuka',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'success',
            self::STATUS_SELESAI => 'dark',
            default => 'secondary',
        };
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeUntukKelas(Builder $query, int $kelasId): Builder
    {
        return $query->where('kelas_id', $kelasId);
    }
}
