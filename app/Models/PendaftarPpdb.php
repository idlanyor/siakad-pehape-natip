<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PendaftarPpdb extends Model
{
    protected $fillable = [
        'no_pendaftaran', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'nisn', 'alamat', 'email', 'no_hp', 'asal_sekolah',
        'nama_ayah', 'nama_ibu', 'no_hp_ortu', 'pekerjaan_ortu',
        'jurusan_pilihan_id', 'jurusan_pilihan_2_id', 'nilai_rata_rata',
        'foto', 'dok_ijazah', 'dok_rapor', 'dok_kk', 'dok_akta',
        'status', 'catatan_admin', 'kelas_diterima_id',
        'diproses_oleh', 'diproses_at',
        'gelombang', 'tahun_ajaran',
    ];

    protected $casts = [
        'tanggal_lahir'   => 'date',
        'nilai_rata_rata' => 'float',
        'diproses_at'     => 'datetime',
    ];

    const STATUS_LIST = ['pending', 'verifikasi', 'diterima', 'ditolak'];

    const STATUS_LABELS = [
        'pending'     => 'Menunggu',
        'verifikasi'  => 'Verifikasi',
        'diterima'    => 'Diterima',
        'ditolak'     => 'Ditolak',
    ];

    const STATUS_COLORS = [
        'pending'     => 'secondary',
        'verifikasi'  => 'info',
        'diterima'    => 'success',
        'ditolak'     => 'danger',
    ];

    const DOKUMEN_FIELDS = [
        'foto'       => 'Foto (3x4)',
        'dok_ijazah' => 'Ijazah / SKL',
        'dok_rapor'  => 'Rapor SMP/MTs',
        'dok_kk'     => 'Kartu Keluarga',
        'dok_akta'   => 'Akta Kelahiran',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function jurusanPilihan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_pilihan_id');
    }

    public function jurusanPilihan2()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_pilihan_2_id');
    }

    public function kelasDiterima()
    {
        return $this->belongsTo(Kelas::class, 'kelas_diterima_id');
    }

    public function diproses()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function getJkLabelAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Generate nomor pendaftaran unik: PPDB-{tahun}-{nnn}
     */
    public static function generateNoPendaftaran(?string $tahunAjaran = null): string
    {
        $tahun = substr($tahunAjaran ?? date('Y') . '/' . (date('Y') + 1), 0, 4);

        $last = static::where('no_pendaftaran', 'LIKE', "PPDB-{$tahun}-%")
            ->orderBy('no_pendaftaran', 'desc')
            ->value('no_pendaftaran');

        if ($last) {
            $lastNum = (int) substr($last, strrpos($last, '-') + 1);
            $next = $lastNum + 1;
        } else {
            $next = 1;
        }

        return sprintf("PPDB-%s-%05d", $tahun, $next);
    }

    /**
     * Terima pendaftar: buat User + Siswa, update status.
     *
     * @param  int|null $kelasId  Kelas tujuan
     * @return array{user: User, siswa: Siswa}
     */
    public function terima(?int $kelasId = null): array
    {
        // Buat user account
        $username = 'siswa' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
        $password = strtolower(str_replace(' ', '', $this->nama)) . date('Y');

        $user = User::create([
            'name'     => $this->nama,
            'username' => $username,
            'email'    => $this->email ?? $username . '@smk48.sch.id',
            'password' => Hash::make($password),
            'role'     => 2, // Siswa
        ]);

        // Buat siswa
        $siswa = Siswa::create([
            'user_id'       => $user->id,
            'nama'          => $this->nama,
            'nisn'          => $this->nisn,
            'tanggal_lahir' => $this->tanggal_lahir,
            'jurusan_id'    => $this->jurusan_pilihan_id,
            'kelas_id'      => $kelasId ?? $this->kelas_diterima_id,
        ]);

        // Update status pendaftar
        $this->update([
            'status'           => 'diterima',
            'kelas_diterima_id' => $kelasId ?? $this->kelas_diterima_id,
            'diproses_oleh'    => auth()->id(),
            'diproses_at'      => now(),
        ]);

        return [
            'user'     => $user,
            'siswa'    => $siswa,
            'username' => $username,
            'password' => $password,
        ];
    }

    /**
     * Tolak pendaftar.
     */
    public function tolak(?string $catatan = null): void
    {
        $this->update([
            'status'        => 'ditolak',
            'catatan_admin' => $catatan,
            'diproses_oleh' => auth()->id(),
            'diproses_at'   => now(),
        ]);
    }
}
