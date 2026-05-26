<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumumans';

    protected $fillable = [
        'judul', 'isi', 'target_role', 'target_kelas_id', 'dibuat_oleh', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'target_role'  => 'integer',
    ];

    const TARGET_LABELS = [
        null => 'Semua',
        0    => 'Admin',
        1    => 'Guru',
        2    => 'Siswa',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function targetKelas()
    {
        return $this->belongsTo(Kelas::class, 'target_kelas_id');
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeForUser($query, User $user)
    {
        return $query->published()->where(function ($q) use ($user) {
            // target semua role
            $q->whereNull('target_role')
              // atau target role cocok
              ->orWhere('target_role', $user->role);
        })->where(function ($q) use ($user) {
            // tidak ada filter kelas ATAU siswa ada di kelas itu
            $q->whereNull('target_kelas_id');
            if ($user->role === 2) {
                $siswa = $user->siswa;
                if ($siswa) {
                    $q->orWhere('target_kelas_id', $siswa->kelas_id);
                }
            }
        });
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getIsPublishedAttribute(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    public function getTargetLabelAttribute(): string
    {
        $label = self::TARGET_LABELS[$this->target_role] ?? 'Semua';
        if ($this->target_kelas_id && $this->targetKelas) {
            $label .= ' — Kelas ' . $this->targetKelas->nama_kelas;
        }
        return $label;
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Kirim notifikasi ke semua user yang menjadi target pengumuman ini.
     */
    public function broadcastNotifikasi(): void
    {
        $query = User::query();

        if ($this->target_role !== null) {
            $query->where('role', $this->target_role);
        }

        if ($this->target_kelas_id) {
            // Hanya siswa di kelas tersebut
            $siswaIds = Siswa::where('kelas_id', $this->target_kelas_id)->pluck('user_id');
            $query->whereIn('id', $siswaIds);
        }

        $users = $query->get();

        foreach ($users as $user) {
            Notifikasi::create([
                'user_id' => $user->id,
                'judul'   => 'Pengumuman: ' . $this->judul,
                'pesan'   => \Str::limit(strip_tags($this->isi), 120),
                'url'     => route('pengumuman.show', $this->id),
            ]);
        }
    }
}
