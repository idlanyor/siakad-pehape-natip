<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = ['jadwal_id', 'siswa_id', 'tanggal', 'status', 'keterangan', 'dicatat_oleh'];

    protected $casts = ['tanggal' => 'date'];

    const STATUS_LIST = ['Hadir', 'Izin', 'Sakit', 'Alpa'];
    const THRESHOLD_ALPA = 3; // warning jika Alpa >= 3x

    public function jadwal()  { return $this->belongsTo(Jadwal::class); }
    public function siswa()   { return $this->belongsTo(Siswa::class); }
    public function pencatat(){ return $this->belongsTo(User::class, 'dicatat_oleh'); }

    public function getBadgeColorAttribute(): string
    {
        return match($this->status) {
            'Hadir' => 'success',
            'Izin'  => 'warning',
            'Sakit' => 'info',
            'Alpa'  => 'danger',
            default => 'secondary',
        };
    }
}
