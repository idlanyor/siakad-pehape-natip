<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $fillable = [
        'jadwal_id', 'guru_id', 'judul', 'deskripsi',
        'tipe', 'konten', 'file_name', 'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Notifikasi ke siswa saat materi terbit (baru dibuat / baru diterbitkan).
        static::created(function (Materi $materi) {
            \App\Services\ELearningNotifier::materiTerbit($materi);
        });

        static::updated(function (Materi $materi) {
            if ($materi->wasChanged('is_published') && $materi->is_published) {
                \App\Services\ELearningNotifier::materiTerbit($materi);
            }
        });
    }

    const TIPE_LABEL = [
        'file' => 'File',
        'link' => 'Tautan',
        'teks' => 'Teks',
    ];

    const TIPE_ICON = [
        'file' => 'fas fa-file-alt',
        'link' => 'fas fa-link',
        'teks' => 'fas fa-align-left',
    ];

    const TIPE_COLOR = [
        'file' => 'primary',
        'link' => 'info',
        'teks' => 'success',
    ];

    public function jadwal() { return $this->belongsTo(Jadwal::class); }
    public function guru()   { return $this->belongsTo(Guru::class); }

    public function getTipeLabelAttribute(): string
    {
        return self::TIPE_LABEL[$this->tipe] ?? $this->tipe;
    }

    public function getTipeIconAttribute(): string
    {
        return self::TIPE_ICON[$this->tipe] ?? 'fas fa-file';
    }

    public function getTipeColorAttribute(): string
    {
        return self::TIPE_COLOR[$this->tipe] ?? 'secondary';
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->tipe === 'file') {
            return asset('storage/' . $this->konten);
        }
        if ($this->tipe === 'link') {
            return $this->konten;
        }
        return null;
    }
}
