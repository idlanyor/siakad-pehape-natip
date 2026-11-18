<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    public function index()
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();

        $jadwals = Jadwal::with(['mapel', 'kelas', 'semester.tahunAjaran'])
            ->where('guru_id', $guru->id)
            ->urutHari()
            ->orderBy('jam_mulai')
            ->get();

        return view('guru.nilai.index', compact('jadwals'));
    }

    public function form(Jadwal $jadwal)
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $jadwal->load(['mapel', 'kelas', 'semester.tahunAjaran']);

        $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)->orderBy('nama')->get();

        $existing = Nilai::where('jadwal_id', $jadwal->id)
            ->get()
            ->keyBy('siswa_id');

        return view('guru.nilai.form', compact('jadwal', 'siswas', 'existing'));
    }

    public function simpan(Request $request, Jadwal $jadwal)
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $request->validate([
            'nilai'              => 'required|array',
            'nilai.*.tugas'      => 'nullable|numeric|min:0|max:100',
            'nilai.*.pts'        => 'nullable|numeric|min:0|max:100',
            'nilai.*.pas'        => 'nullable|numeric|min:0|max:100',
            'nilai.*.praktik'    => 'nullable|numeric|min:0|max:100',
            'nilai.*.proyek'     => 'nullable|numeric|min:0|max:100',
            'nilai.*.portofolio' => 'nullable|numeric|min:0|max:100',
            'nilai.*.catatan'    => 'nullable|string|max:500',
        ]);

        foreach ($request->nilai as $siswaId => $data) {
            Nilai::updateOrCreate(
                ['jadwal_id' => $jadwal->id, 'siswa_id' => $siswaId],
                [
                    'tugas'        => $data['tugas'] ?? null,
                    'pts'          => $data['pts'] ?? null,
                    'pas'          => $data['pas'] ?? null,
                    'praktik'      => $data['praktik'] ?? null,
                    'proyek'       => $data['proyek'] ?? null,
                    'portofolio'   => $data['portofolio'] ?? null,
                    'catatan'      => $data['catatan'] ?? null,
                    'dicatat_oleh' => auth()->id(),
                ]
            );
        }

        return redirect()->route('guru.nilai.index')
            ->with('success', 'Nilai berhasil disimpan.');
    }

    public function rekap(Jadwal $jadwal)
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $jadwal->load(['mapel', 'kelas', 'semester.tahunAjaran']);

        $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)->orderBy('nama')->get();

        $nilais = Nilai::where('jadwal_id', $jadwal->id)
            ->get()
            ->keyBy('siswa_id');

        return view('guru.nilai.rekap', compact('jadwal', 'siswas', 'nilais'));
    }
}
