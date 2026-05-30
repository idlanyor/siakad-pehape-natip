<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, \App\Traits\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'avatar',
        'password',
        'role',
    ];

    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    public function guru()
    {
        return $this->hasOne(Guru::class);
    }

    public function pendaftarPpdb()
    {
        return $this->hasOne(\App\Models\PendaftarPpdb::class);
    }

    public function orangTua()
    {
        return $this->hasOne(OrangTua::class);
    }

    public function isAdmin(): bool
    {
        return $this->role == 0;
    }

    public function isGuru(): bool
    {
        return $this->role == 1;
    }

    public function isSiswa(): bool
    {
        return $this->role == 2;
    }

    public function isPendaftar(): bool
    {
        return $this->role == 3;
    }

    public function isOrangTua(): bool
    {
        return $this->role == 4;
    }

    /**
     * URL avatar untuk ditampilkan. Pakai foto yang diunggah bila ada,
     * jika tidak fallback ke avatar default AdminLTE.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->avatar)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->avatar);
        }

        return asset('dist/img/avatar.png');
    }

    /**
     * 2FA aktif & sudah dikonfirmasi (siap dipakai saat login).
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled
            && ! is_null($this->two_factor_confirmed_at)
            && ! is_null($this->two_factor_secret);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
