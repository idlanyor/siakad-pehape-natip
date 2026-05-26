<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Siswa;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    // List semua jadwal milik guru → pilih jadwal + tanggal
    public function index()
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();

        $jadwals = Jadwal::with(['mapel', 'kelas', 'semester.tahunAjaran'])
            ->where('guru_id', $guru->id)
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        return view('guru.absensi.index', compact('jadwals'));
    }

    // Form input absensi: jadwal tertentu, tanggal tertentu
    public function form(Request $request, Jadwal $jadwal)
    {
        // Pastikan jadwal ini milik guru yang login
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $tanggal = $request->input('tanggal', today()->toDateString());

        $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)
            ->orderBy('nama')
            ->get();

        // Ambil absensi yang sudah ada untuk tanggal ini
        $existing = Absensi::where('jadwal_id', $jadwal->id)
            ->where('tanggal', $tanggal)
            ->pluck('status', 'siswa_id'); // [siswa_id => status]

        $sudahDiisi = $existing->isNotEmpty();

        return view('guru.absensi.form', compact('jadwal', 'siswas', 'tanggal', 'existing', 'sudahDiisi'));
    }

    // Simpan / update absensi
    public function simpan(Request $request, Jadwal $jadwal)
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $request->validate([
            'tanggal'        => 'required|date',
            'status'         => 'required|array',
            'status.*'       => 'required|in:Hadir,Izin,Sakit,Alpa',
            'keterangan'     => 'nullable|array',
            'keterangan.*'   => 'nullable|string|max:200',
        ]);

        foreach ($request->status as $siswaId => $status) {
            Absensi::updateOrCreate(
                ['jadwal_id' => $jadwal->id, 'siswa_id' => $siswaId, 'tanggal' => $request->tanggal],
                [
                    'status'       => $status,
                    'keterangan'   => $request->keterangan[$siswaId] ?? null,
                    'dicatat_oleh' => auth()->id(),
                ]
            );
        }

        return redirect()->route('guru.absensi.index')
            ->with('success', 'Absensi berhasil disimpan untuk tanggal ' . $request->tanggal);
    }

    // Rekap absensi per jadwal (semua pertemuan)
    public function rekap(Jadwal $jadwal)
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)->orderBy('nama')->get();

        // Ambil semua pertemuan (tanggal unik) untuk jadwal ini
        $pertemuan = Absensi::where('jadwal_id', $jadwal->id)
            ->selectRaw('tanggal')
            ->distinct()
            ->orderBy('tanggal')
            ->pluck('tanggal');

        // Pivot: [siswa_id][tanggal] => status
        $pivot = Absensi::where('jadwal_id', $jadwal->id)
            ->get()
            ->groupBy('siswa_id')
            ->map(fn($rows) => $rows->pluck('status', 'tanggal'));

        return view('guru.absensi.rekap', compact('jadwal', 'siswas', 'pertemuan', 'pivot'));
    }
}
