<?php

namespace App\Models;

use App\Support\PasswordAwal;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PendaftarPpdb extends Model
{
    use Auditable;

    /** Jangan pernah catat kredensial di audit log. */
    public array $auditExclude = ['akun_password_plain', 'akun_username'];

    protected $fillable = [
        'user_id',
        'no_pendaftaran', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'nisn', 'alamat', 'email', 'no_hp', 'asal_sekolah',
        'nama_ayah', 'nama_ibu', 'no_hp_ortu', 'pekerjaan_ortu',
        'jurusan_pilihan_id', 'jurusan_pilihan_2_id', 'nilai_rata_rata',
        'foto', 'dok_ijazah', 'dok_rapor', 'dok_kk', 'dok_akta',
        'status', 'catatan_admin', 'kelas_diterima_id',
        'diproses_oleh', 'diproses_at',
        'gelombang', 'gelombang_id', 'tahun_ajaran',
        'akun_username', 'akun_password_plain',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'nilai_rata_rata' => 'float',
        'diproses_at' => 'datetime',
        'akun_password_plain' => 'encrypted',
    ];

    /**
     * Sembunyikan password (terenkripsi) dari serialisasi array/JSON
     * agar tidak bocor lewat API atau response yang men-serialize model.
     */
    protected $hidden = [
        'akun_password_plain',
    ];

    const STATUS_LIST = ['pending', 'verifikasi', 'diterima', 'ditolak'];

    /** Password awal akun pendaftar/siswa: acak, bukan NISN. Lihat {@see PasswordAwal}. */
    public static function buatPasswordAwal(): string
    {
        return PasswordAwal::buat();
    }

    const STATUS_LABELS = [
        'pending' => 'Menunggu',
        'verifikasi' => 'Verifikasi',
        'diterima' => 'Diterima',
        'ditolak' => 'Ditolak',
    ];

    const STATUS_COLORS = [
        'pending' => 'secondary',
        'verifikasi' => 'info',
        'diterima' => 'success',
        'ditolak' => 'danger',
    ];

    const DOKUMEN_FIELDS = [
        'foto' => 'Foto (3x4)',
        'dok_ijazah' => 'Ijazah / SKL',
        'dok_rapor' => 'Rapor SMP/MTs',
        'dok_kk' => 'Kartu Keluarga',
        'dok_akta' => 'Akta Kelahiran',
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

    public function gelombangPpdb()
    {
        return $this->belongsTo(GelombangPpdb::class, 'gelombang_id');
    }

    public function userAkun()
    {
        return $this->belongsTo(User::class, 'user_id');
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
        $tahun = substr($tahunAjaran ?? date('Y').'/'.(date('Y') + 1), 0, 4);

        $last = static::where('no_pendaftaran', 'LIKE', "PPDB-{$tahun}-%")
            ->orderBy('no_pendaftaran', 'desc')
            ->value('no_pendaftaran');

        if ($last) {
            $lastNum = (int) substr($last, strrpos($last, '-') + 1);
            $next = $lastNum + 1;
        } else {
            $next = 1;
        }

        return sprintf('PPDB-%s-%05d', $tahun, $next);
    }

    /**
     * Terima pendaftar: buat User + Siswa, update status.
     *
     * @param  int|null  $kelasId  Kelas tujuan
     * @return array{user: User, siswa: Siswa}
     */
    public function terima(?int $kelasId = null): array
    {
        // Cek NISN tidak duplikat
        if ($this->nisn && Siswa::where('nisn', $this->nisn)->exists()) {
            throw new \RuntimeException("NISN {$this->nisn} sudah terdaftar sebagai siswa.");
        }

        // Jika sudah punya akun pendaftar (role=3), upgrade role-nya ke siswa (role=2)
        if ($this->user_id && $existingUser = User::find($this->user_id)) {
            $user = $existingUser;
            $username = $user->username;
            $password = $this->akun_password_plain; // password sudah tersimpan saat daftar

            $user->update(['role' => 2]);
        } else {
            // Fallback: buat user baru (untuk data lama sebelum fitur pendaftar login)
            $base = 'siswa'.str_pad($this->id, 4, '0', STR_PAD_LEFT);
            $username = $base;
            $suffix = 1;
            while (User::where('username', $username)->exists()) {
                $username = $base.$suffix++;
            }

            $password = self::buatPasswordAwal();

            $user = User::create([
                'name' => $this->nama,
                'username' => $username,
                'email' => $this->email,
                'password' => Hash::make($password),
                'role' => 2,
            ]);
        }

        $siswa = Siswa::create([
            'user_id' => $user->id,
            'nama' => $this->nama,
            'nisn' => $this->nisn,
            'tanggal_lahir' => $this->tanggal_lahir,
            'jurusan_id' => $this->jurusan_pilihan_id,
            'kelas_id' => $kelasId ?? $this->kelas_diterima_id,
        ]);

        $this->update([
            'status' => 'diterima',
            'kelas_diterima_id' => $kelasId ?? $this->kelas_diterima_id,
            'diproses_oleh' => auth()->id(),
            'diproses_at' => now(),
            'akun_username' => $username,
            'akun_password_plain' => $password,
        ]);

        return compact('user', 'siswa', 'username', 'password');
    }

    /**
     * Tolak pendaftar.
     */
    public function tolak(?string $catatan = null): void
    {
        $this->update([
            'status' => 'ditolak',
            'catatan_admin' => $catatan,
            'diproses_oleh' => auth()->id(),
            'diproses_at' => now(),
        ]);
    }

    /**
     * Hapus semua file dokumen dari storage.
     */
    public function hapusDokumen(): void
    {
        foreach (array_keys(self::DOKUMEN_FIELDS) as $field) {
            if ($this->$field) {
                Storage::disk('public')->delete($this->$field);
            }
        }
    }
}
