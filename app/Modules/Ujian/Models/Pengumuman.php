<?php

namespace App\Modules\Ujian\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pengumuman proktor untuk seluruh peserta satu ujian. Dikirim ke browser
 * siswa lewat respons ping.
 */
class Pengumuman extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'ujian_pengumuman';

    protected $fillable = ['ujian_id', 'user_id', 'pesan'];

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Bentuk yang dikirim ke browser siswa/proktor. */
    public function ringkas(): array
    {
        return [
            'id' => $this->id,
            'pesan' => $this->pesan,
            'oleh' => $this->user?->name,
            'waktu' => $this->created_at,
        ];
    }
}
