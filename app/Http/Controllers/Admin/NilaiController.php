<?php

namespace App\Http\Controllers\Admin;

use App\Exports\NilaiExport;
use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Semester;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NilaiController extends Controller
{
    public function index()
    {
        $semester = Semester::aktif();

        $jadwals = Jadwal::with(['mapel', 'kelas', 'guru', 'semester.tahunAjaran'])
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->get()
            ->map(function ($j) {
                $j->jumlah_siswa   = Siswa::where('kelas_id', $j->kelas_id)->count();
                $j->jumlah_dinilai = Nilai::where('jadwal_id', $j->id)->count();
                return $j;
            });

        return view('admin.nilai.index', compact('jadwals', 'semester'));
    }

    public function detail(Jadwal $jadwal)
    {
        $jadwal->load(['mapel', 'kelas', 'guru', 'semester.tahunAjaran']);

        $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)->orderBy('nama')->get();

        $nilais = Nilai::where('jadwal_id', $jadwal->id)
            ->get()
            ->keyBy('siswa_id');

        return view('admin.nilai.detail', compact('jadwal', 'siswas', 'nilais'));
    }

    public function raport(Request $request)
    {
        $semester = Semester::aktif();
        $kelasList = Kelas::with('jurusan')->orderBy('tingkat')->orderBy('nama_kelas')->get();

        $siswa  = null;
        $jadwals = collect();
        $nilais  = collect();
        $siswas  = collect();

        if ($request->filled('kelas_id')) {
            $siswas = Siswa::where('kelas_id', $request->kelas_id)->orderBy('nama')->get();
        }

        if ($request->filled('siswa_id')) {
            $siswa = Siswa::with(['kelas', 'jurusan'])->findOrFail($request->siswa_id);

            $jadwals = Jadwal::with(['mapel'])
                ->where('kelas_id', $siswa->kelas_id)
                ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
                ->get();

            $nilais = Nilai::where('siswa_id', $siswa->id)
                ->whereIn('jadwal_id', $jadwals->pluck('id'))
                ->get()
                ->keyBy('jadwal_id');
        }

        return view('admin.nilai.raport', compact('semester', 'kelasList', 'siswas', 'siswa', 'jadwals', 'nilais'));
    }

    public function exportNilai(Jadwal $jadwal)
    {
        $nama = 'Nilai_' . str_replace(' ', '_', $jadwal->mapel->nama_mapel ?? 'Mapel') . '_' . ($jadwal->kelas->nama_kelas ?? '') . '.xlsx';
        return Excel::download(new NilaiExport($jadwal), $nama);
    }

    public function raportPdf(Request $request)
    {
        if (!$request->filled('siswa_id')) {
            return back()->with('error', 'Pilih siswa terlebih dahulu.');
        }

        $semester = Semester::aktif();
        $siswa    = Siswa::with(['kelas', 'jurusan'])->findOrFail($request->siswa_id);

        $jadwals = Jadwal::with(['mapel'])
            ->where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->get();

        $nilais = Nilai::where('siswa_id', $siswa->id)
            ->whereIn('jadwal_id', $jadwals->pluck('id'))
            ->get()->keyBy('jadwal_id');

        $pdf = Pdf::loadView('admin.nilai.raport-pdf', compact('siswa', 'jadwals', 'nilais', 'semester'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Raport_' . str_replace(' ', '_', $siswa->nama) . '.pdf');
    }
}
