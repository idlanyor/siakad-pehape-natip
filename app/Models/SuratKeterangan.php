<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratKeterangan extends Model
{
    protected $fillable = ['siswa_id', 'jenis_surat', 'nomor_surat', 'tanggal_surat', 'keterangan', 'dicatat_oleh'];

    protected $casts = [
        'tanggal_surat' => 'date',
    ];

    const JENIS_LABELS = [
        'aktif'  => 'Surat Keterangan Aktif',
        'pindah' => 'Surat Keterangan Pindah',
        'lulus'  => 'Surat Keterangan Lulus',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS_LABELS[$this->jenis_surat] ?? $this->jenis_surat;
    }
}
