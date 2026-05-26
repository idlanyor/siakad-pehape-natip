<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $fillable = ['user_id', 'jurusan_id', 'kelas_id', 'nama', 'nisn', 'tanggal_lahir'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }

    public function penempatanPkl()
    {
        return $this->hasMany(PenempatanPkl::class);
    }

    public function pelanggarans()
    {
        return $this->hasMany(Pelanggaran::class);
    }

    public function prestasis()
    {
        return $this->hasMany(Prestasi::class);
    }

    public function anggotaEkskuls()
    {
        return $this->hasMany(AnggotaEkskul::class);
    }

    public function suratKeterangans()
    {
        return $this->hasMany(SuratKeterangan::class);
    }

    public function tagihanSpps()
    {
        return $this->hasMany(TagihanSpp::class);
    }
}
