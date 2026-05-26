<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempatPkl extends Model
{
    protected $fillable = ['nama', 'bidang_usaha', 'alamat', 'kontak_person', 'no_hp', 'kuota'];

    public function penempatans() { return $this->hasMany(PenempatanPkl::class, 'tempat_pkl_id'); }

    public function getSiswaAktifCountAttribute(): int
    {
        return $this->penempatans()->where('status', 'aktif')->count();
    }
}
