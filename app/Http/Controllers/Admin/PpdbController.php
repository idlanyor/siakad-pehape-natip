<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PpdbExport;
use App\Http\Controllers\Controller;
use App\Models\GelombangPpdb;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Notifikasi;
use App\Models\PendaftarPpdb;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PpdbController extends Controller
{
    public function index(Request $request)
    {
        $query = PendaftarPpdb::with(['jurusanPilihan', 'kelasDiterima', 'gelombangPpdb'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('jurusan_id')) $query->where('jurusan_pilihan_id', $request->jurusan_id);
        if ($request->filled('gelombang_id')) $query->where('gelombang_id', $request->gelombang_id);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($qb) => $qb
                ->where('nama', 'LIKE', "%{$q}%")
                ->orWhere('no_pendaftaran', 'LIKE', "%{$q}%")
                ->orWhere('asal_sekolah', 'LIKE', "%{$q}%")
                ->orWhere('nisn', 'LIKE', "%{$q}%")
            );
        }

        $pendaftars = $query->paginate(25)->withQueryString();

        $stats = [
            'total'      => PendaftarPpdb::count(),
            'pending'    => PendaftarPpdb::where('status', 'pending')->count(),
            'verifikasi' => PendaftarPpdb::where('status', 'verifikasi')->count(),
            'diterima'   => PendaftarPpdb::where('status', 'diterima')->count(),
            'ditolak'    => PendaftarPpdb::where('status', 'ditolak')->count(),
        ];

        $jurusans   = Jurusan::orderBy('nama_jurusan')->get();
        $gelombangList = GelombangPpdb::orderBy('tanggal_buka', 'desc')->get();

        return view('admin.ppdb.index', compact('pendaftars', 'stats', 'jurusans', 'gelombangList'));
    }

    public function show(PendaftarPpdb $pendaftar)
    {
        $pendaftar->load(['jurusanPilihan', 'jurusanPilihan2', 'kelasDiterima', 'diproses', 'gelombangPpdb']);
        $kelasList = Kelas::with('jurusan')
            ->where('jurusan_id', $pendaftar->jurusan_pilihan_id)
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.ppdb.show', compact('pendaftar', 'kelasList'));
    }

    public function verifikasi(PendaftarPpdb $pendaftar)
    {
        abort_if($pendaftar->status === 'diterima', 422, 'Tidak bisa mengubah status pendaftar yang sudah diterima.');

        $pendaftar->update([
            'status'        => 'verifikasi',
            'diproses_oleh' => auth()->id(),
            'diproses_at'   => now(),
        ]);

        // Notifikasi jika pendaftar punya akun (setelah diterima sudah ada user)
        // Di sini hanya update status, belum ada user — skip notif

        return back()->with('success', 'Status diubah ke Verifikasi.');
    }

    public function terima(Request $request, PendaftarPpdb $pendaftar)
    {
        $request->validate(['kelas_id' => 'required|exists:kelas,id']);

        if ($pendaftar->status === 'diterima') {
            return back()->with('error', 'Pendaftar ini sudah diterima sebelumnya.');
        }

        try {
            $result = $pendaftar->terima($request->kelas_id);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Notifikasi ke akun siswa baru
        Notifikasi::create([
            'user_id' => $result['user']->id,
            'judul'   => 'Selamat! Anda Diterima di ' . setting('sekolah_nama', 'SMK'),
            'pesan'   => "Halo {$pendaftar->nama}, pendaftaran Anda telah diterima. " .
                         "Username: {$result['username']} | Password: {$result['password']}. " .
                         "Silakan login dan segera ganti password.",
            'url'     => '/dashboard',
        ]);

        return redirect()->route('admin.ppdb.show', $pendaftar)
            ->with('terima_result', [
                'username' => $result['username'],
                'password' => $result['password'],
                'nama'     => $pendaftar->nama,
            ]);
    }

    public function tolak(Request $request, PendaftarPpdb $pendaftar)
    {
        $request->validate(['catatan_admin' => 'nullable|string|max:500']);

        if ($pendaftar->status === 'diterima') {
            return back()->with('error', 'Tidak bisa menolak pendaftar yang sudah diterima.');
        }

        $pendaftar->tolak($request->catatan_admin);

        return redirect()->route('admin.ppdb.show', $pendaftar)
            ->with('success', "{$pendaftar->nama} telah ditolak.");
    }

    public function export(Request $request)
    {
        $status = $request->status ?? 'all';
        return Excel::download(new PpdbExport($status), 'PPDB_' . $status . '_' . date('Ymd') . '.xlsx');
    }

    public function laporan()
    {
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();

        // Fix: gunakan query per jurusan, bukan clone
        $rekapJurusan = $jurusans->map(function (Jurusan $j) {
            $base = PendaftarPpdb::where('jurusan_pilihan_id', $j->id);
            return [
                'jurusan'    => $j,
                'total'      => (int) (clone $base)->count(),
                'pending'    => (int) (clone $base)->where('status', 'pending')->count(),
                'verifikasi' => (int) (clone $base)->where('status', 'verifikasi')->count(),
                'diterima'   => (int) (clone $base)->where('status', 'diterima')->count(),
                'ditolak'    => (int) (clone $base)->where('status', 'ditolak')->count(),
            ];
        })->filter(fn($r) => $r['total'] > 0)->values();

        $statsGlobal = [
            'total'    => PendaftarPpdb::count(),
            'diterima' => PendaftarPpdb::where('status', 'diterima')->count(),
            'ditolak'  => PendaftarPpdb::where('status', 'ditolak')->count(),
            'menunggu' => PendaftarPpdb::whereIn('status', ['pending', 'verifikasi'])->count(),
        ];

        // Per gelombang menggunakan JOIN yang proper
        $gelombangs = GelombangPpdb::withCount([
            'pendaftars',
            'pendaftars as diterima_count' => fn($q) => $q->where('status', 'diterima'),
            'pendaftars as ditolak_count'  => fn($q) => $q->where('status', 'ditolak'),
        ])->orderBy('tanggal_buka')->get();

        return view('admin.ppdb.laporan', compact('rekapJurusan', 'statsGlobal', 'gelombangs'));
    }
}
