<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class UserSiswa extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user_siswa';

    protected $fillable = [
        'siswa_id',
        'username',
        'password',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'login_attempts',
        'locked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'login_attempts' => 'integer',
    ];

    /**
     * Relasi ke siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /** True — UserSiswa selalu siswa */
    public function isAdmin(): bool { return false; }
    public function isGuru(): bool  { return false; }
    public function isSiswa(): bool { return true; }
    public function hasTwoFactorEnabled(): bool { return false; }
    public function getAvatarUrlAttribute(): string
    {
        return asset('dist/img/avatar5.png');
    }

    /**
     * Check if account is locked
     */
    public function isLocked(): bool
    {
        if (!$this->locked_until) {
            return false;
        }

        if ($this->locked_until->isFuture()) {
            return true;
        }

        // Unlock if time has passed
        $this->update([
            'locked_until' => null,
            'login_attempts' => 0,
        ]);

        return false;
    }

    /**
     * Increment login attempts
     */
    public function incrementLoginAttempts(): void
    {
        $this->increment('login_attempts');

        // Lock account after 5 failed attempts for 15 minutes
        if ($this->login_attempts >= 5) {
            $this->update([
                'locked_until' => now()->addMinutes(15),
            ]);
        }
    }

    /**
     * Reset login attempts
     */
    public function resetLoginAttempts(): void
    {
        $this->update([
            'login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    /**
     * Update last login info
     */
    public function updateLastLogin(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
            'login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    /**
     * Generate default password (NIS)
     */
    public static function generateDefaultPassword(string $nis): string
    {
        return $nis;
    }

    /**
     * Create or update user siswa from siswa data
     */
    public static function createOrUpdateFromSiswa(Siswa $siswa): self
    {
        $username = $siswa->nis;
        $defaultPassword = self::generateDefaultPassword($siswa->nis);

        return self::updateOrCreate(
            ['siswa_id' => $siswa->id],
            [
                'username' => $username,
                'password' => Hash::make($defaultPassword),
                'is_active' => true,
            ]
        );
    }

    /**
     * Reset password to default (NIS)
     */
    public function resetPasswordToDefault(): void
    {
        $defaultPassword = self::generateDefaultPassword($this->siswa->nis);
        
        $this->update([
            'password' => Hash::make($defaultPassword),
            'login_attempts' => 0,
            'locked_until' => null,
        ]);
    }
}
