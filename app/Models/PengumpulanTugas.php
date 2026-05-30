<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengumpulanTugas extends Model
{
    protected $fillable = [
        'tugas_id', 'siswa_id', 'konten', 'file_name',
        'catatan', 'nilai', 'feedback', 'dinilai_at', 'dinilai_oleh',
    ];

    protected $casts = [
        'nilai'      => 'float',
        'dinilai_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Notifikasi ke siswa saat tugasnya baru dinilai (nilai diisi).
        static::updated(function (PengumpulanTugas $p) {
            if ($p->wasChanged('nilai') && $p->nilai !== null) {
                \App\Services\ELearningNotifier::tugasDinilai($p);
            }
        });
    }

    public function tugas()    { return $this->belongsTo(Tugas::class); }
    public function siswa()    { return $this->belongsTo(Siswa::class); }
    public function penilai()  { return $this->belongsTo(User::class, 'dinilai_oleh'); }

    public function getIsDinilaiAttribute(): bool
    {
        return $this->nilai !== null;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_dinilai) return 'Sudah Dinilai';
        return 'Menunggu Penilaian';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->is_dinilai ? 'success' : 'warning';
    }

    public function getDownloadUrlAttribute(): ?string
    {
        $tipe = $this->tugas?->tipe_pengumpulan;
        if ($tipe === 'file') {
            return asset('storage/' . $this->konten);
        }
        return null;
    }

    public function getNilaiPersenAttribute(): ?float
    {
        if ($this->nilai === null || $this->tugas === null) return null;
        return round(($this->nilai / $this->tugas->nilai_maksimal) * 100, 1);
    }
}
