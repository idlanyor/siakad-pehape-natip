<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $fillable = ['user_id', 'judul', 'pesan', 'url', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    // ─── Methods ───────────────────────────────────────────────────────────────

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }
}
