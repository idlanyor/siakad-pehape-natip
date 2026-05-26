<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TagihanSpp extends Model
{
    protected $fillable = ['siswa_id', 'bulan', 'nominal', 'status', 'keterangan', 'dicatat_oleh'];

    protected $casts = [
        'bulan'   => 'date',
        'nominal' => 'decimal:2',
    ];

    // Daftar bulan untuk generate tagihan
    public static array $STATUS_LIST = ['belum', 'cicil', 'lunas'];

    public static array $STATUS_LABELS = [
        'belum' => 'Belum Bayar',
        'cicil' => 'Cicilan',
        'lunas' => 'Lunas',
    ];

    public static array $STATUS_COLORS = [
        'belum' => 'danger',
        'cicil' => 'warning',
        'lunas' => 'success',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function pembayarans()
    {
        return $this->hasMany(PembayaranSpp::class);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getTotalDibayarAttribute(): float
    {
        return (float) $this->pembayarans()->sum('nominal_bayar');
    }

    public function getSisaTagihanAttribute(): float
    {
        return max(0, (float) $this->nominal - $this->total_dibayar);
    }

    public function getBulanLabelAttribute(): string
    {
        return $this->bulan ? $this->bulan->translatedFormat('F Y') : '-';
    }

    public function getNominalRpAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::$STATUS_COLORS[$this->status] ?? 'secondary';
    }

    // ─── Methods ───────────────────────────────────────────────────────────────

    /**
     * Hitung ulang dan simpan status berdasarkan total pembayaran yang masuk.
     */
    public function recalculateStatus(): void
    {
        $total = $this->pembayarans()->sum('nominal_bayar');

        if ($total <= 0) {
            $status = 'belum';
        } elseif ($total >= $this->nominal) {
            $status = 'lunas';
        } else {
            $status = 'cicil';
        }

        $this->update(['status' => $status]);
    }

    /**
     * Generate tagihan batch untuk semua siswa aktif di suatu kelas pada bulan tertentu.
     * Nominal diambil dari TarifSpp kelas + tahun ajaran aktif.
     *
     * @param  Kelas  $kelas
     * @param  string $bulan  format: 'Y-m' (e.g. '2026-01')
     * @param  float  $nominal
     * @return int    jumlah tagihan yang berhasil dibuat (bukan di-skip)
     */
    public static function generateBatch(Kelas $kelas, string $bulan, float $nominal): int
    {
        $tanggalBulan = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth()->toDateString();
        $siswas = $kelas->siswas()->get();
        $dibuat = 0;

        foreach ($siswas as $siswa) {
            $created = self::firstOrCreate(
                ['siswa_id' => $siswa->id, 'bulan' => $tanggalBulan],
                ['nominal' => $nominal, 'status' => 'belum', 'dicatat_oleh' => auth()->id()]
            );
            if ($created->wasRecentlyCreated) {
                $dibuat++;
            }
        }

        return $dibuat;
    }
}
