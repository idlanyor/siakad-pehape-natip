<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProktorEvent extends Model
{
    use HasFactory;

    protected $table = 'proktor_event';

    public $timestamps = false; // Hanya created_at

    protected $fillable = [
        'sesi_ujian_id',
        'tipe',
        'detail',
    ];

    protected $casts = [
        'detail' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke sesi ujian
     */
    public function sesiUjian(): BelongsTo
    {
        return $this->belongsTo(SesiUjian::class, 'sesi_ujian_id');
    }

    /**
     * Scope untuk tipe event tertentu
     */
    public function scopeTipe($query, string $tipe)
    {
        return $query->where('tipe', $tipe);
    }

    /**
     * Scope untuk event tab switch
     */
    public function scopeTabSwitch($query)
    {
        return $query->where('tipe', 'tab_switch');
    }

    /**
     * Scope untuk event copy/paste
     */
    public function scopeCopyPaste($query)
    {
        return $query->whereIn('tipe', ['copy', 'paste']);
    }

    /**
     * Scope untuk event dalam rentang waktu
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Get label tipe event yang human-readable
     */
    public function getTipeLabel(): string
    {
        return match($this->tipe) {
            'tab_switch' => 'Pindah Tab',
            'blur' => 'Keluar Fokus',
            'copy' => 'Copy',
            'paste' => 'Paste',
            'fullscreen_exit' => 'Keluar Fullscreen',
            'reconnect' => 'Reconnect',
            'right_click' => 'Klik Kanan',
            'devtools_open' => 'Buka DevTools',
            default => $this->tipe,
        };
    }

    /**
     * Get severity level untuk event
     */
    public function getSeverity(): string
    {
        return match($this->tipe) {
            'tab_switch', 'blur', 'fullscreen_exit' => 'high',
            'copy', 'paste', 'right_click' => 'medium',
            'reconnect' => 'low',
            'devtools_open' => 'critical',
            default => 'low',
        };
    }
}