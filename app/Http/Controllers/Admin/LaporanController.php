<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\GelombangPpdb;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\PendaftarPpdb;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /** Menu laporan. */
    public function index()
    {
        return view('admin.laporan.index', [
            'kelasList'    => Kelas::orderBy('nama_kelas')->get(),
            'gelombangList'=> GelombangPpdb::orderByDesc('id')->get(),
        ]);
    }

    /** Laporan kehadiran per kelas. */
    public function kehadiran(Request $request)
    {
        $request->validate(['kelas_id' => 'required|exists:kelas,id']);
        $kelas = Kelas::with('jurusan')->findOrFail($request->kelas_id);
        $semester = Semester::aktif();

        $siswas = Siswa::where('kelas_id', $kelas->id)->orderBy('nama')->get();
        $rows = $siswas->map(function (Siswa $s) {
            $abs = Absensi::where('siswa_id', $s->id);
            return [
                'nama'  => $s->nama,
                'nisn'  => $s->nisn,
                'hadir' => (clone $abs)->where('status', 'Hadir')->count(),
                'izin'  => (clone $abs)->where('status', 'Izin')->count(),
                'sakit' => (clone $abs)->where('status', 'Sakit')->count(),
                'alpa'  => (clone $abs)->where('status', 'Alpa')->count(),
            ];
        });

        return $this->output('kehadiran', compact('kelas', 'semester', 'rows'), $request,
            'Laporan_Kehadiran_' . $kelas->nama_kelas);
    }

    /** Laporan rekap nilai akhir per kelas. */
    public function nilai(Request $request)
    {
        $request->validate(['kelas_id' => 'required|exists:kelas,id']);
        $kelas = Kelas::with('jurusan')->findOrFail($request->kelas_id);
        $semester = Semester::aktif();

        $siswas = Siswa::where('kelas_id', $kelas->id)->orderBy('nama')->get();
        $rows = $siswas->map(function (Siswa $s) {
            $nilais = Nilai::where('siswa_id', $s->id)->get();
            $avg = $nilais->map(fn($n) => $n->nilai_akhir)->filter()->avg();
            return [
                'nama'    => $s->nama,
                'nisn'    => $s->nisn,
                'jml_mapel' => $nilais->count(),
                'rata'    => $avg ? round($avg, 2) : null,
            ];
        });

        return $this->output('nilai', compact('kelas', 'semester', 'rows'), $request,
            'Laporan_Nilai_' . $kelas->nama_kelas);
    }

    /** Laporan keuangan SPP per bulan. */
    public function keuangan(Request $request)
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        [$year, $month] = explode('-', $bulan);

        $tagihans = TagihanSpp::with('siswa.kelas')
            ->whereYear('bulan', $year)->whereMonth('bulan', $month)
            ->get();

        $summary = [
            'total'    => $tagihans->count(),
            'lunas'    => $tagihans->where('status', 'lunas')->count(),
            'belum'    => $tagihans->where('status', 'belum')->count(),
            'cicil'    => $tagihans->where('status', 'cicil')->count(),
            'nominal'  => $tagihans->sum('nominal'),
            'terkumpul'=> $tagihans->sum(fn($t) => (float) $t->total_dibayar),
        ];

        return $this->output('keuangan', compact('tagihans', 'summary', 'bulan'), $request,
            'Laporan_Keuangan_' . $bulan);
    }

    /** Laporan PPDB per gelombang. */
    public function ppdb(Request $request)
    {
        $gelombang = $request->filled('gelombang_id')
            ? GelombangPpdb::find($request->gelombang_id) : null;

        $q = PendaftarPpdb::query()
            ->when($gelombang, fn($q) => $q->where('gelombang_id', $gelombang->id));

        $rows = (clone $q)->get();
        $summary = [
            'total'     => $rows->count(),
            'pending'   => $rows->whereIn('status', ['pending', 'verifikasi'])->count(),
            'diterima'  => $rows->where('status', 'diterima')->count(),
            'ditolak'   => $rows->where('status', 'ditolak')->count(),
        ];

        return $this->output('ppdb', compact('rows', 'summary', 'gelombang'), $request,
            'Laporan_PPDB');
    }

    /**
     * Render HTML / unduh PDF sesuai parameter `export`.
     */
    private function output(string $jenis, array $data, Request $request, string $filename)
    {
        $view = "admin.laporan.$jenis";

        if ($request->input('export') === 'pdf') {
            return Pdf::loadView($view . '-pdf', $data)
                ->setPaper('a4', $jenis === 'kehadiran' ? 'landscape' : 'portrait')
                ->download($filename . '.pdf');
        }

        return view($view, $data);
    }
}
