<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Semester;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    private array $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function index()
    {
        $semester = Semester::aktif();
        $jadwals  = Jadwal::with(['mapel', 'kelas', 'guru', 'semester'])
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
            ->orderBy('jam_mulai')
            ->get();

        $grouped  = $jadwals->groupBy('hari');
        $kelas    = Kelas::with('jurusan')->get();

        return view('admin.jadwal.index', compact('jadwals', 'grouped', 'kelas', 'semester'));
    }

    public function create()
    {
        return view('admin.jadwal.form', [
            'jadwal'   => null,
            'mapels'  => Mapel::orderBy('nama_mapel')->get(),
            'kelas'    => Kelas::with('jurusan')->get(),
            'gurus'    => Guru::orderBy('nama')->get(),
            'semesters'=> Semester::with('tahunAjaran')->get(),
            'hariList' => $this->hariList,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mapel_id'   => 'required|exists:mapels,id',
            'kelas_id'    => 'required|exists:kelas,id',
            'guru_id'     => 'required|exists:gurus,id',
            'semester_id' => 'required|exists:semesters,id',
            'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'ruangan'     => 'nullable|string|max:50',
            'link_zoom'   => 'nullable|url',
        ]);

        $conflict = $this->detectConflict($request);
        if ($conflict) {
            return back()->withInput()->with('error', $conflict);
        }

        Jadwal::create($request->only([
            'mapel_id', 'kelas_id', 'guru_id', 'semester_id',
            'hari', 'jam_mulai', 'jam_selesai', 'ruangan', 'link_zoom',
        ]));

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Jadwal $jadwal)
    {
        return view('admin.jadwal.form', [
            'jadwal'   => $jadwal,
            'mapels'  => Mapel::orderBy('nama_mapel')->get(),
            'kelas'    => Kelas::with('jurusan')->get(),
            'gurus'    => Guru::orderBy('nama')->get(),
            'semesters'=> Semester::with('tahunAjaran')->get(),
            'hariList' => $this->hariList,
        ]);
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $request->validate([
            'mapel_id'   => 'required|exists:mapels,id',
            'kelas_id'    => 'required|exists:kelas,id',
            'guru_id'     => 'required|exists:gurus,id',
            'semester_id' => 'required|exists:semesters,id',
            'hari'        => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'ruangan'     => 'nullable|string|max:50',
            'link_zoom'   => 'nullable|url',
        ]);

        $conflict = $this->detectConflict($request, $jadwal->id);
        if ($conflict) {
            return back()->withInput()->with('error', $conflict);
        }

        $jadwal->update($request->only([
            'mapel_id', 'kelas_id', 'guru_id', 'semester_id',
            'hari', 'jam_mulai', 'jam_selesai', 'ruangan', 'link_zoom',
        ]));

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal)
    {
        $jadwal->delete();
        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    // ── Conflict detection ────────────────────────────────────────────────
    private function detectConflict(Request $request, ?int $excludeId = null): ?string
    {
        $base = Jadwal::where('semester_id', $request->semester_id)
            ->where('hari', $request->hari)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($request) {
                // Overlap: start < other.end AND end > other.start
                $q->where('jam_mulai', '<', $request->jam_selesai)
                  ->where('jam_selesai', '>', $request->jam_mulai);
            });

        // 1. Kelas bentrok
        if ((clone $base)->where('kelas_id', $request->kelas_id)->exists()) {
            $kelas = Kelas::find($request->kelas_id);
            return "Bentrok jadwal: kelas {$kelas->nama_kelas} sudah ada jadwal di jam tersebut.";
        }

        // 2. Guru bentrok
        if ((clone $base)->where('guru_id', $request->guru_id)->exists()) {
            $guru = Guru::find($request->guru_id);
            return "Bentrok jadwal: guru {$guru->nama} sudah mengajar di jam tersebut.";
        }

        // 3. Ruangan bentrok (hanya jika ruangan diisi)
        if ($request->filled('ruangan')) {
            if ((clone $base)->where('ruangan', $request->ruangan)->exists()) {
                return "Bentrok jadwal: ruangan {$request->ruangan} sudah digunakan di jam tersebut.";
            }
        }

        return null;
    }
}
