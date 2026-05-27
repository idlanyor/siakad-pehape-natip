<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    protected $fillable = [
        'jadwal_id', 'guru_id', 'judul', 'instruksi',
        'deadline', 'nilai_maksimal', 'tipe_pengumpulan',
    ];

    protected $casts = [
        'deadline'       => 'datetime',
        'nilai_maksimal' => 'integer',
    ];

    const TIPE_LABEL = [
        'file' => 'Upload File',
        'link' => 'Tautan (URL)',
        'teks' => 'Teks / Essay',
    ];

    public function jadwal()        { return $this->belongsTo(Jadwal::class); }
    public function guru()          { return $this->belongsTo(Guru::class); }
    public function pengumpulans()  { return $this->hasMany(PengumpulanTugas::class); }

    public function pengumpulanSiswa(int $siswaId): ?PengumpulanTugas
    {
        return $this->pengumpulans()->where('siswa_id', $siswaId)->first();
    }

    public function getTipeLabelAttribute(): string
    {
        return self::TIPE_LABEL[$this->tipe_pengumpulan] ?? $this->tipe_pengumpulan;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->deadline->isPast();
    }

    public function getSisaWaktuAttribute(): string
    {
        if ($this->is_expired) {
            return 'Sudah lewat ' . $this->deadline->diffForHumans();
        }
        return 'Sisa ' . $this->deadline->diffForHumans(absolute: true);
    }

    public function getDeadlineColorAttribute(): string
    {
        if ($this->is_expired) return 'danger';
        if ($this->deadline->diffInHours() < 24) return 'warning';
        return 'success';
    }

    public function getJumlahKumpulAttribute(): int
    {
        return $this->pengumpulans()->count();
    }

    public function getJumlahDinilaiAttribute(): int
    {
        return $this->pengumpulans()->whereNotNull('nilai')->count();
    }
}
