<?php

namespace App\Modules\Ujian\Models;

use App\Models\User;
use Database\Factories\Ujian\ProktorEventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log kejadian pada sesi ujian: pelanggaran yang dilaporkan browser siswa,
 * atau aksi proktor (paksa selesai, diskualifikasi, reset, pengumuman).
 */
class ProktorEvent extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    /** Pelanggaran yang dilaporkan dari browser siswa => [label, severity]. */
    public const PELANGGARAN = [
        'tab_switch' => ['Pindah Tab', 'high'],
        'blur' => ['Keluar Fokus', 'high'],
        'fullscreen_exit' => ['Keluar Fullscreen', 'high'],
        'copy' => ['Copy', 'medium'],
        'paste' => ['Paste', 'medium'],
        'right_click' => ['Klik Kanan', 'medium'],
        'devtools_open' => ['Buka DevTools', 'critical'],
        'reconnect' => ['Reconnect', 'low'],
    ];

    /** Aksi proktor => label. */
    public const AKSI = [
        'force_finish' => 'Dipaksa Selesai',
        'disqualify' => 'Diskualifikasi',
        'reset' => 'Sesi Direset',
        'announce' => 'Pengumuman',
        'extend_time' => 'Tambah Waktu',
        'device_switch' => 'Pindah Perangkat',
    ];

    protected $table = 'proktor_event';

    protected $fillable = ['sesi_ujian_id', 'tipe', 'keterangan', 'detail', 'user_id'];

    protected $casts = [
        'detail' => 'array',
    ];

    protected static function newFactory(): ProktorEventFactory
    {
        return ProktorEventFactory::new();
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class, 'sesi_ujian_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPelanggaran(): bool
    {
        return array_key_exists($this->tipe, self::PELANGGARAN);
    }

    public function getTipeLabelAttribute(): string
    {
        return self::PELANGGARAN[$this->tipe][0] ?? self::AKSI[$this->tipe] ?? $this->tipe;
    }

    public function getSeverityAttribute(): string
    {
        return self::PELANGGARAN[$this->tipe][1] ?? 'info';
    }

    public function scopePelanggaran(Builder $query): Builder
    {
        return $query->whereIn('tipe', array_keys(self::PELANGGARAN));
    }

    public function scopeTipe(Builder $query, string $tipe): Builder
    {
        return $query->where('tipe', $tipe);
    }
}
