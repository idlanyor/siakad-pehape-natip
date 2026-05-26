<?php

namespace App\Http\Controllers;

use App\Models\FaqPpdb;
use App\Models\GelombangPpdb;
use App\Models\Jurusan;
use App\Models\PendaftarPpdb;
use App\Models\PersyaratanPpdb;
use Illuminate\Http\Request;

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
            'foto'                 => 'nullable|image|max:2048',
            'dok_ijazah'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'dok_rapor'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'dok_kk'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'dok_akta'             => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

        $data = $request->except(array_keys(PendaftarPpdb::DOKUMEN_FIELDS));
        $data['no_pendaftaran'] = PendaftarPpdb::generateNoPendaftaran($request->tahun_ajaran ?? setting('ppdb_tahun_ajaran'));
        $data['status']         = 'pending';

        foreach (array_keys(PendaftarPpdb::DOKUMEN_FIELDS) as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('ppdb/' . $field, 'public');
            }
        }

        $pendaftar = PendaftarPpdb::create($data);

        return redirect()->route('ppdb.sukses', $pendaftar)
            ->with('success', 'Pendaftaran berhasil!');
    }

    /**
     * Halaman sukses setelah mendaftar.
     */
    public function sukses(PendaftarPpdb $pendaftar)
    {
        return view('ppdb.sukses', compact('pendaftar'));
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
        $request->validate(['no_pendaftaran' => 'required|string']);

        $pendaftar = PendaftarPpdb::with(['jurusanPilihan', 'kelasDiterima'])
            ->where('no_pendaftaran', $request->no_pendaftaran)
            ->first();

        if (!$pendaftar) {
            return back()->withInput()
                ->with('error', 'Nomor pendaftaran tidak ditemukan.');
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
