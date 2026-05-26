<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Notifikasi;
use App\Models\PendaftarPpdb;
use Illuminate\Http\Request;

class PpdbController extends Controller
{
    public function index(Request $request)
    {
        $query = PendaftarPpdb::with(['jurusanPilihan', 'kelasDiterima'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_pilihan_id', $request->jurusan_id);
        }
        if ($request->filled('gelombang')) {
            $query->where('gelombang', $request->gelombang);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qb) use ($q) {
                $qb->where('nama', 'LIKE', "%{$q}%")
                    ->orWhere('no_pendaftaran', 'LIKE', "%{$q}%")
                    ->orWhere('asal_sekolah', 'LIKE', "%{$q}%");
            });
        }

        $pendaftars = $query->paginate(25)->withQueryString();

        // Statistik
        $stats = [
            'total'      => PendaftarPpdb::count(),
            'pending'    => PendaftarPpdb::where('status', 'pending')->count(),
            'verifikasi' => PendaftarPpdb::where('status', 'verifikasi')->count(),
            'diterima'   => PendaftarPpdb::where('status', 'diterima')->count(),
            'ditolak'    => PendaftarPpdb::where('status', 'ditolak')->count(),
        ];

        $jurusans  = Jurusan::orderBy('nama_jurusan')->get();

        return view('admin.ppdb.index', compact('pendaftars', 'stats', 'jurusans'));
    }

    public function show(PendaftarPpdb $pendaftar)
    {
        $pendaftar->load(['jurusanPilihan', 'jurusanPilihan2', 'kelasDiterima', 'diproses']);
        $kelasList = Kelas::with('jurusan')
            ->where('jurusan_id', $pendaftar->jurusan_pilihan_id)
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.ppdb.show', compact('pendaftar', 'kelasList'));
    }

    /**
     * Update status ke verifikasi (admin sudah review berkas).
     */
    public function verifikasi(PendaftarPpdb $pendaftar)
    {
        $pendaftar->update([
            'status'        => 'verifikasi',
            'diproses_oleh' => auth()->id(),
        ]);

        return back()->with('success', 'Status diubah ke Verifikasi.');
    }

    /**
     * Terima pendaftar → buat akun User + Siswa otomatis.
     */
    public function terima(Request $request, PendaftarPpdb $pendaftar)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        if ($pendaftar->status === 'diterima') {
            return back()->with('error', 'Pendaftar ini sudah diterima sebelumnya.');
        }

        $result = $pendaftar->terima($request->kelas_id);

        return redirect()->route('admin.ppdb.show', $pendaftar)
            ->with('success', "Selamat! {$pendaftar->nama} telah diterima.\n" .
                "Username: {$result['username']}\n" .
                "Password: {$result['password']}");
    }

    /**
     * Tolak pendaftar.
     */
    public function tolak(Request $request, PendaftarPpdb $pendaftar)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        if ($pendaftar->status === 'diterima') {
            return back()->with('error', 'Tidak bisa menolak pendaftar yang sudah diterima.');
        }

        $pendaftar->tolak($request->catatan_admin);

        return redirect()->route('admin.ppdb.show', $pendaftar)
            ->with('success', "{$pendaftar->nama} telah ditolak.");
    }

    /**
     * Laporan PPDB — statistik per jurusan, gelombang.
     */
    public function laporan()
    {
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();

        $rekapJurusan = $jurusans->map(function (Jurusan $j) {
            $all = PendaftarPpdb::where('jurusan_pilihan_id', $j->id);
            return [
                'jurusan'  => $j,
                'total'    => (clone $all)->count(),
                'pending'  => (clone $all)->where('status', 'pending')->count(),
                'verifikasi' => (clone $all)->where('status', 'verifikasi')->count(),
                'diterima' => (clone $all)->where('status', 'diterima')->count(),
                'ditolak'  => (clone $all)->where('status', 'ditolak')->count(),
            ];
        });

        $statsGlobal = [
            'total'      => PendaftarPpdb::count(),
            'diterima'   => PendaftarPpdb::where('status', 'diterima')->count(),
            'ditolak'    => PendaftarPpdb::where('status', 'ditolak')->count(),
            'menunggu'   => PendaftarPpdb::whereIn('status', ['pending', 'verifikasi'])->count(),
        ];

        // Per gelombang
        $gelombangs = PendaftarPpdb::selectRaw('gelombang, COUNT(*) as total, SUM(CASE WHEN status="diterima" THEN 1 ELSE 0 END) as diterima')
            ->groupBy('gelombang')
            ->orderBy('gelombang')
            ->get();

        return view('admin.ppdb.laporan', compact('rekapJurusan', 'statsGlobal', 'gelombangs'));
    }
}
