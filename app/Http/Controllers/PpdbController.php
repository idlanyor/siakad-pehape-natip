<?php

namespace App\Http\Controllers;

use App\Models\FaqPpdb;
use App\Models\GelombangPpdb;
use App\Models\Jurusan;
use App\Models\PendaftarPpdb;
use App\Models\PersyaratanPpdb;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PpdbController extends Controller
{
    /**
     * Halaman landing portal PPDB (info gelombang, syarat, FAQ, countdown).
     */
    public function landing()
    {
        $gelombangAktif  = GelombangPpdb::buka()->first();
        $gelombangSemua  = GelombangPpdb::aktif()->orderBy('tanggal_buka')->get();
        $persyaratanList = PersyaratanPpdb::aktif()->get();
        $faqList         = FaqPpdb::aktif()->get();
        $jurusans        = Jurusan::where('is_aktif_ppdb', true)->orderBy('nama_jurusan')->get();

        return view('ppdb.landing', compact(
            'gelombangAktif', 'gelombangSemua', 'persyaratanList', 'faqList', 'jurusans'
        ));
    }

    /**
     * Form pendaftaran.
     */
    public function index()
    {
        $jurusans = Jurusan::where('is_aktif_ppdb', true)->orderBy('nama_jurusan')->get();

        // Cek apakah ada gelombang yang buka
        $gelombangBuka = GelombangPpdb::buka()->first();

        return view('ppdb.index', compact('jurusans', 'gelombangBuka'));
    }

    /**
     * Simpan pendaftaran baru.
     * Berkas dokumen TIDAK diupload di sini — dilakukan via dashboard pendaftar.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'                 => 'required|string|max:255',
            'jenis_kelamin'        => 'required|in:L,P',
            'tempat_lahir'         => 'required|string|max:100',
            'tanggal_lahir'        => 'required|date|before:today',
            'nisn'                 => 'nullable|string|max:20',
            'alamat'               => 'required|string',
            'email'                => 'nullable|email|max:255',
            'no_hp'                => 'required|string|max:20',
            'asal_sekolah'         => 'required|string|max:255',
            'nama_ayah'            => 'nullable|string|max:255',
            'nama_ibu'             => 'nullable|string|max:255',
            'no_hp_ortu'           => 'nullable|string|max:20',
            'pekerjaan_ortu'       => 'nullable|string|max:100',
            'jurusan_pilihan_id'   => 'required|exists:jurusans,id',
            'jurusan_pilihan_2_id' => 'nullable|exists:jurusans,id|different:jurusan_pilihan_id',
            'nilai_rata_rata'      => 'nullable|numeric|min:0|max:100',
        ]);

        // Ambil gelombang yang sedang buka
        $gelombang = GelombangPpdb::buka()->first();

        if (!$gelombang) {
            return back()->withInput()
                ->with('error', 'Saat ini tidak ada gelombang pendaftaran yang dibuka. Silakan cek jadwal PPDB.');
        }

        // Cek kuota
        $sisa = $gelombang->sisa_kuota;
        if ($sisa !== null && $sisa <= 0) {
            return back()->withInput()
                ->with('error', 'Kuota pendaftaran gelombang ini sudah penuh. Tunggu gelombang berikutnya.');
        }

        $data                   = $request->all();
        $data['no_pendaftaran'] = PendaftarPpdb::generateNoPendaftaran($gelombang->tahun_ajaran);
        $data['status']         = 'pending';
        $data['gelombang_id']   = $gelombang->id;
        $data['gelombang']      = $gelombang->id;
        $data['tahun_ajaran']   = $gelombang->tahun_ajaran;

        $pendaftar = PendaftarPpdb::create($data);

        // Buat akun login untuk pendaftar (role=3)
        [$username, $password] = $this->buatAkunPendaftar($pendaftar);

        return redirect()->route('ppdb.sukses', $pendaftar)
            ->with('akun_username', $username)
            ->with('akun_password', $password)
            ->with('success', 'Pendaftaran berhasil!');
    }

    /**
     * Buat User role=3 untuk pendaftar baru, simpan ke pendaftar_ppdbs.
     */
    private function buatAkunPendaftar(PendaftarPpdb $pendaftar): array
    {
        // Generate username: ppdb + 5 digit id
        $base     = 'ppdb' . str_pad($pendaftar->id, 5, '0', STR_PAD_LEFT);
        $username = $base;
        $suffix   = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $suffix++;
        }

        // Password: NISN jika ada, fallback ke 8 char random
        $password = $pendaftar->nisn
            ? $pendaftar->nisn
            : substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);

        $user = User::create([
            'name'     => $pendaftar->nama,
            'username' => $username,
            'email'    => $pendaftar->email,
            'password' => Hash::make($password),
            'role'     => 3,
        ]);

        $pendaftar->update([
            'user_id'             => $user->id,
            'akun_username'       => $username,
            'akun_password_plain' => $password,
        ]);

        return [$username, $password];
    }

    /**
     * Halaman sukses setelah mendaftar.
     */
    public function sukses(PendaftarPpdb $pendaftar)
    {
        // Ambil dari session (baru daftar) atau dari model (akses ulang)
        $akunUsername = session('akun_username', $pendaftar->akun_username);
        $akunPassword = session('akun_password', $pendaftar->akun_password_plain);

        return view('ppdb.sukses', compact('pendaftar', 'akunUsername', 'akunPassword'));
    }

    /**
     * Cetak bukti pendaftaran (print-friendly, tanpa layout).
     */
    public function cetakBukti(PendaftarPpdb $pendaftar)
    {
        return view('ppdb.cetak', compact('pendaftar'));
    }

    /**
     * Form cek status pendaftaran.
     */
    public function cekForm()
    {
        return view('ppdb.cek');
    }

    /**
     * Cari status berdasarkan no pendaftaran.
     */
    public function cekStatus(Request $request)
    {
        $request->validate([
            'no_pendaftaran' => 'nullable|string',
            'nisn'           => 'nullable|string',
            'tanggal_lahir'  => 'nullable|date',
        ]);

        $pendaftar = null;

        // Cara 1: via nomor pendaftaran
        if ($request->filled('no_pendaftaran')) {
            $pendaftar = PendaftarPpdb::with(['jurusanPilihan', 'kelasDiterima'])
                ->where('no_pendaftaran', $request->no_pendaftaran)
                ->first();
        }

        // Cara 2: via NISN + tanggal lahir
        if (!$pendaftar && $request->filled('nisn') && $request->filled('tanggal_lahir')) {
            $pendaftar = PendaftarPpdb::with(['jurusanPilihan', 'kelasDiterima'])
                ->where('nisn', $request->nisn)
                ->whereDate('tanggal_lahir', $request->tanggal_lahir)
                ->first();
        }

        if (!$pendaftar) {
            return back()->withInput()
                ->with('error', 'Data tidak ditemukan. Periksa kembali nomor pendaftaran atau NISN dan tanggal lahir.');
        }

        return view('ppdb.cek', compact('pendaftar'));
    }

    /**
     * Halaman persyaratan lengkap.
     */
    public function syarat()
    {
        $persyaratanList = PersyaratanPpdb::aktif()->get();
        return view('ppdb.syarat', compact('persyaratanList'));
    }
}
