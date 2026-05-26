<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggotaOrganisasi extends Model
{
    protected $fillable = [
        'jabatan_organisasi_id', 'nama', 'guru_id', 'foto', 'nip', 'periode', 'is_aktif',
    ];

    protected $casts = ['is_aktif' => 'boolean'];

    public function jabatan()
    {
        return $this->belongsTo(JabatanOrganisasi::class, 'jabatan_organisasi_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function getFotoUrlAttribute(): ?string
    {
        if ($this->guru_id && $this->guru) {
            return null; // foto guru dikelola terpisah jika ada
        }
        return $this->foto ? asset('storage/' . $this->foto) : null;
    }
}
