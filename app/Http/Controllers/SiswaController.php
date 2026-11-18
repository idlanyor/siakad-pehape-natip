<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Mapel;
use App\Models\Pengumuman;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TagihanSpp;
use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\Ujian;

class SiswaController extends Controller
{
    public function dashboard()
    {
        $siswa = Siswa::with(['jurusan', 'kelas'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $hariMap = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
        $hariIni = $hariMap[now()->englishDayOfWeek] ?? now()->englishDayOfWeek;

        $semester = Semester::where('aktif', true)->first();

        // Jadwal hari ini
        $jadwalHariIni = Jadwal::with(['mapel', 'guru'])
            ->where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn ($q) => $q->where('semester_id', $semester->id))
            ->where('hari', $hariIni)
            ->orderBy('jam_mulai')
            ->get();

        // Jumlah mapel semester aktif
        $jumlahMapel = Jadwal::where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn ($q) => $q->where('semester_id', $semester->id))
            ->distinct('mapel_id')
            ->count('mapel_id');

        // Rekap absensi semester ini
        $jadwalIds = Jadwal::where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn ($q) => $q->where('semester_id', $semester->id))
            ->pluck('id');

        $absensiRekap = Absensi::where('siswa_id', $siswa->id)
            ->whereIn('jadwal_id', $jadwalIds)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Tagihan SPP belum lunas
        $tagihanBelumLunas = TagihanSpp::where('siswa_id', $siswa->id)
            ->whereIn('status', ['belum', 'cicil'])
            ->orderBy('bulan')
            ->get();

        // Pengumuman untuk siswa
        $pengumumans = Pengumuman::where(function ($q) use ($siswa) {
            $q->whereNull('target_role')
                ->orWhere('target_role', 2)
                ->orWhere(fn ($q2) => $q2->where('target_role', 2)->where('target_kelas_id', $siswa->kelas_id));
        })
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        // Ujian online yang sedang dibuka untuk kelas siswa dan belum selesai dikerjakan.
        $ujianTersedia = Ujian::query()
            ->published()
            ->where('kelas_id', $siswa->kelas_id)
            ->where('waktu_mulai', '<=', now())
            ->where('waktu_selesai', '>=', now())
            ->whereDoesntHave('sesi', fn ($q) => $q
                ->where('siswa_id', $siswa->id)
                ->whereIn('status', SesiUjian::STATUS_BERAKHIR))
            ->count();

        return view('siswa.dashboard', compact(
            'siswa', 'jadwalHariIni', 'jumlahMapel',
            'absensiRekap', 'tagihanBelumLunas', 'pengumumans',
            'hariIni', 'semester', 'ujianTersedia'
        ));
    }

    public function jadwal()
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $jadwals = Jadwal::with(['mapel', 'guru'])
            ->where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn ($q) => $q->where('semester_id', $semester->id))
            ->urutHari()
            ->orderBy('jam_mulai')
            ->get();

        $grouped = $jadwals->groupBy('hari');

        return view('siswa.jadwal', compact('jadwals', 'grouped', 'siswa', 'semester'));
    }

    public function mapel()
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $mapels = Mapel::whereHas('jadwals', function ($q) use ($siswa, $semester) {
            $q->where('kelas_id', $siswa->kelas_id);
            if ($semester) {
                $q->where('semester_id', $semester->id);
            }
        })->with(['jadwals' => function ($q) use ($siswa, $semester) {
            $q->where('kelas_id', $siswa->kelas_id);
            if ($semester) {
                $q->where('semester_id', $semester->id);
            }
        }, 'jurusan', 'gurus'])->get();

        return view('siswa.mapel', compact('mapels', 'siswa', 'semester'));
    }
}
