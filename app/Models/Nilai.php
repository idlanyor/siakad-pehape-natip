<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $fillable = [
        'jadwal_id', 'siswa_id',
        'tugas', 'pts', 'pas',
        'praktik', 'proyek', 'portofolio',
        'catatan', 'dicatat_oleh',
    ];

    protected $casts = [
        'tugas' => 'float', 'pts' => 'float', 'pas' => 'float',
        'praktik' => 'float', 'proyek' => 'float', 'portofolio' => 'float',
    ];

    const KOMPONEN_PENGETAHUAN  = ['tugas', 'pts', 'pas'];
    const KOMPONEN_KETERAMPILAN = ['praktik', 'proyek', 'portofolio'];
    const KOMPONEN_ALL = ['tugas', 'pts', 'pas', 'praktik', 'proyek', 'portofolio'];

    const KOMPONEN_LABEL = [
        'tugas'      => 'Tugas',
        'pts'        => 'PTS',
        'pas'        => 'PAS',
        'praktik'    => 'Praktik',
        'proyek'     => 'Proyek',
        'portofolio' => 'Portofolio',
    ];

    public function jadwal()   { return $this->belongsTo(Jadwal::class); }
    public function siswa()    { return $this->belongsTo(Siswa::class); }
    public function pencatat() { return $this->belongsTo(User::class, 'dicatat_oleh'); }

    public function getRataPengetahuanAttribute(): ?float
    {
        $vals = collect(self::KOMPONEN_PENGETAHUAN)->map(fn($k) => $this->$k)->filter(fn($v) => $v !== null);
        return $vals->isEmpty() ? null : round($vals->avg(), 2);
    }

    public function getRataKeterampilanAttribute(): ?float
    {
        $vals = collect(self::KOMPONEN_KETERAMPILAN)->map(fn($k) => $this->$k)->filter(fn($v) => $v !== null);
        return $vals->isEmpty() ? null : round($vals->avg(), 2);
    }

    public function getNilaiAkhirAttribute(): ?float
    {
        $vals = collect([$this->rata_pengetahuan, $this->rata_keterampilan])->filter(fn($v) => $v !== null);
        return $vals->isEmpty() ? null : round($vals->avg(), 2);
    }

    public function getPredikatAttribute(): string
    {
        $n = $this->nilai_akhir;
        if ($n === null) return '-';
        return match (true) {
            $n >= 90 => 'A',
            $n >= 80 => 'B',
            $n >= 70 => 'C',
            default  => 'D',
        };
    }

    public function getPredikatColorAttribute(): string
    {
        return match ($this->predikat) {
            'A' => 'success',
            'B' => 'primary',
            'C' => 'warning',
            'D' => 'danger',
            default => 'secondary',
        };
    }
}
