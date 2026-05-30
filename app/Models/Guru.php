<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use \App\Traits\Auditable;

    protected $fillable = ['user_id', 'nip', 'nama', 'bidang_studi', 'no_hp', 'no_wa'];

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

    public function mapels()
    {
        return $this->belongsToMany(Mapel::class, 'guru_mapel')->withTimestamps();
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
