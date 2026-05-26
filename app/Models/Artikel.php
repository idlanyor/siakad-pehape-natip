<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Artikel extends Model
{
    protected $fillable = [
        'judul', 'slug', 'ringkasan', 'konten', 'thumbnail',
        'kategori', 'is_published', 'published_at', 'dibuat_oleh',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    const KATEGORI = [
        'berita'      => 'Berita',
        'pengumuman'  => 'Pengumuman',
        'prestasi'    => 'Prestasi',
        'agenda'      => 'Agenda',
    ];

    const KATEGORI_COLORS = [
        'berita'      => 'primary',
        'pengumuman'  => 'warning',
        'prestasi'    => 'success',
        'agenda'      => 'info',
    ];

    // ─── Relasi ───────────────────────────────────────────────────────────────

    public function penulis()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(fn($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getKategoriLabelAttribute(): string
    {
        return self::KATEGORI[$this->kategori] ?? $this->kategori;
    }

    public function getKategoriColorAttribute(): string
    {
        return self::KATEGORI_COLORS[$this->kategori] ?? 'secondary';
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail ? asset('storage/' . $this->thumbnail) : null;
    }

    public function getRingkasanAutoAttribute(): string
    {
        return $this->ringkasan ?: Str::limit(strip_tags($this->konten ?? ''), 150);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public static function generateSlug(string $judul): string
    {
        $base = Str::slug($judul);
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
