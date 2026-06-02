<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SesiUjian;
use App\Models\Ujian;
use Illuminate\Http\Request;

class UjianController extends Controller
{
    public function index(Request $request)
    {
        $query = Ujian::with(['jadwal.mapel', 'jadwal.kelas', 'guru']);

        // Filter kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('jadwal', fn($q) => $q->where('kelas_id', $request->kelas_id));
        }

        // Filter mapel
        if ($request->filled('mapel_id')) {
            $query->whereHas('jadwal', fn($q) => $q->where('mapel_id', $request->mapel_id));
        }

        // Filter guru
        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }

        // Filter tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('waktu_mulai', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('waktu_selesai', '<=', $request->tanggal_selesai);
        }

        // Filter status
        if ($request->filled('status')) {
            $now = now();
            match ($request->status) {
                'draft' => $query->where('is_published', false),
                'belum_mulai' => $query->where('is_published', true)->where('waktu_mulai', '>', $now),
                'berlangsung' => $query->where('is_published', true)->where('waktu_mulai', '<=', $now)->where('waktu_selesai', '>=', $now),
                'selesai' => $query->where('waktu_selesai', '<', $now),
                default => null,
            };
        }

        $ujians = $query->latest('waktu_mulai')->paginate(20);

        // Data filter
        $kelasList = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        $mapelList = Mapel::orderBy('nama_mapel')->get();
        $guruList = Guru::orderBy('nama')->get();

        return view('admin.ujian.index', compact('ujians', 'kelasList', 'mapelList', 'guruList'));
    }

    public function statistik(Ujian $ujian)
    {
        $ujian->load(['jadwal.mapel', 'jadwal.kelas', 'guru']);

        // Ambil semua sesi yang selesai
        $sesis = SesiUjian::with('siswa')
            ->where('ujian_id', $ujian->id)
            ->whereIn('status', ['selesai', 'timeout'])
            ->whereNotNull('nilai_akhir')
            ->get();

        // Hitung statistik
        $stats = [
            'total_peserta' => $ujian->jumlah_peserta,
            'sudah_mengerjakan' => $sesis->count(),
            'persentase_mengerjakan' => $ujian->jumlah_peserta > 0
                ? round(($sesis->count() / $ujian->jumlah_peserta) * 100, 2)
                : 0,
            'rata_rata' => $sesis->avg('nilai_akhir') ?? 0,
            'tertinggi' => $sesis->max('nilai_akhir') ?? 0,
            'terendah' => $sesis->min('nilai_akhir') ?? 0,
            'lulus' => $sesis->where('nilai_akhir', '>=', $ujian->passing_grade)->count(),
            'tidak_lulus' => $sesis->where('nilai_akhir', '<', $ujian->passing_grade)->count(),
            'passing_rate' => $sesis->count() > 0
                ? round(($sesis->where('nilai_akhir', '>=', $ujian->passing_grade)->count() / $sesis->count()) * 100, 2)
                : 0,
        ];

        // Distribusi nilai (histogram)
        $distribusi = [
            '0-20' => $sesis->whereBetween('nilai_akhir', [0, 20])->count(),
            '21-40' => $sesis->whereBetween('nilai_akhir', [21, 40])->count(),
            '41-60' => $sesis->whereBetween('nilai_akhir', [41, 60])->count(),
            '61-80' => $sesis->whereBetween('nilai_akhir', [61, 80])->count(),
            '81-100' => $sesis->whereBetween('nilai_akhir', [81, 100])->count(),
        ];

        // Ranking siswa (top 10)
        $ranking = $sesis->sortByDesc('nilai_akhir')->take(10);

        return view('admin.ujian.statistik', compact('ujian', 'stats', 'distribusi', 'ranking'));
    }
}
