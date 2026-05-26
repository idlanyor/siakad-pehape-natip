<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JabatanOrganisasi extends Model
{
    protected $fillable = ['nama_jabatan', 'parent_id', 'tipe_hubungan', 'urutan', 'deskripsi'];

    public function parent()
    {
        return $this->belongsTo(JabatanOrganisasi::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(JabatanOrganisasi::class, 'parent_id')->orderBy('urutan');
    }

    public function anggota()
    {
        return $this->hasMany(AnggotaOrganisasi::class)->where('is_aktif', true);
    }

    public function semuaAnggota()
    {
        return $this->hasMany(AnggotaOrganisasi::class);
    }
}
