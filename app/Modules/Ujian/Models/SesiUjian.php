<?php

namespace App\Modules\Ujian\Models;

use App\Models\Kelas;
use App\Models\Siswa;
use Database\Factories\Ujian\SesiUjianFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Satu siswa mengerjakan satu ujian.
 */
class SesiUjian extends Model
{
    use HasFactory;

    public const STATUS_BELUM = 'belum';

    public const STATUS_BERLANGSUNG = 'berlangsung';

    public const STATUS_SELESAI = 'selesai';

    public const STATUS_TIMEOUT = 'timeout';

    public const STATUS_DISKUALIFIKASI = 'diskualifikasi';

    /** Status yang berarti sesi sudah berakhir dan tidak bisa dikerjakan lagi. */
    public const STATUS_BERAKHIR = [self::STATUS_SELESAI, self::STATUS_TIMEOUT, self::STATUS_DISKUALIFIKASI];

    public const FASE = ['try_out', 'pelaksanaan', 'remedial'];

    protected $table = 'sesi_ujian';

    protected $fillable = [
        'ujian_id',
        'siswa_id',
        'kelas_id',
        'status',
        'is_resmi',
        'fase',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_detik',
        'nilai_otomatis',
        'nilai_manual',
        'nilai_akhir',
        'predikat',
        'jumlah_pelanggaran',
        'tambahan_detik',
        'token_perangkat',
        'last_activity_at',
    ];

    /** Token perangkat tidak pernah ikut terserialisasi ke props/JSON. */
    protected $hidden = ['token_perangkat'];

    protected $casts = [
        'is_resmi' => 'boolean',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'durasi_detik' => 'integer',
        'nilai_otomatis' => 'decimal:2',
        'nilai_manual' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'jumlah_pelanggaran' => 'integer',
        'tambahan_detik' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    protected static function newFactory(): SesiUjianFactory
    {
        return SesiUjianFactory::new();
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jawaban(): HasMany
    {
        return $this->hasMany(JawabanSiswa::class, 'sesi_ujian_id')->orderBy('urutan');
    }

    public function proktorEvents(): HasMany
    {
        return $this->hasMany(ProktorEvent::class, 'sesi_ujian_id');
    }

    public function isBerlangsung(): bool
    {
        return $this->status === self::STATUS_BERLANGSUNG;
    }

    public function isBerakhir(): bool
    {
        return in_array($this->status, self::STATUS_BERAKHIR, true);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeResmi(Builder $query): Builder
    {
        return $query->where('is_resmi', true);
    }

    public function scopeTryOut(Builder $query): Builder
    {
        return $query->where('is_resmi', false);
    }
}
