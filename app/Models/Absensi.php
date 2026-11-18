<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = ['jadwal_id', 'siswa_id', 'tanggal', 'status', 'keterangan', 'dicatat_oleh'];

    protected $casts = ['tanggal' => 'date'];

    const STATUS_LIST = ['Hadir', 'Izin', 'Sakit', 'Alpa'];

    const THRESHOLD_ALPA = 3; // warning jika Alpa >= 3x

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    /**
     * Rekap absensi satu jadwal: daftar tanggal pertemuan dan status per siswa.
     *
     * Tanggal dinormalkan menjadi string 'Y-m-d'. Kolom `tanggal` di-cast ke
     * Carbon, sehingga pluck('status', 'tanggal') langsung akan memakai objek
     * Carbon sebagai key array dan memicu error.
     *
     * @return array{0: Collection<int, string>, 1: Collection<int, Collection<string, string>>}
     */
    public static function rekapJadwal(Jadwal $jadwal): array
    {
        $rows = static::where('jadwal_id', $jadwal->id)->orderBy('tanggal')->get(['siswa_id', 'tanggal', 'status']);

        $pertemuan = $rows->map(fn (self $a) => $a->tanggal->toDateString())->unique()->values();
        $pivot = $rows->groupBy('siswa_id')
            ->map(fn ($perSiswa) => $perSiswa->mapWithKeys(fn (self $a) => [$a->tanggal->toDateString() => $a->status]));

        return [$pertemuan, $pivot];
    }

    public function getBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'Hadir' => 'success',
            'Izin' => 'warning',
            'Sakit' => 'info',
            'Alpa' => 'danger',
            default => 'secondary',
        };
    }
}
