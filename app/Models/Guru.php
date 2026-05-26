<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $fillable = ['user_id', 'nip', 'nama', 'bidang_studi', 'no_hp'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    public function ekskuls()
    {
        return $this->hasMany(Ekskul::class, 'pembina_id');
    }

    public function permission()
    {
        return $this->hasOne(GuruPermission::class);
    }

    public function canAccess(string $menu): bool
    {
        return $this->permission?->$menu ?? true;
    }
}
