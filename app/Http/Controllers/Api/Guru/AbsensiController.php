<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Siswa;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index()
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();

        $jadwals = Jadwal::with(['mapel', 'kelas'])
            ->where('guru_id', $guru->id)
            ->urutHari()
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn($j) => [
                'id'         => $j->id,
                'mapel'      => $j->mapel->nama_mapel ?? null,
                'kelas'      => $j->kelas->nama_kelas,
                'hari'       => $j->hari,
                'jam_mulai'  => $j->jam_mulai,
                'jam_selesai'=> $j->jam_selesai,
            ]);

        return response()->json(['data' => $jadwals]);
    }

    public function show(Jadwal $jadwal, Request $request)
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $tanggal = $request->input('tanggal', today()->toDateString());

        $siswas = Siswa::where('kelas_id', $jadwal->kelas_id)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nisn']);

        $existing = Absensi::where('jadwal_id', $jadwal->id)
            ->where('tanggal', $tanggal)
            ->pluck('status', 'siswa_id');

        $data = $siswas->map(fn($s) => [
            'siswa_id' => $s->id,
            'nama'     => $s->nama,
            'nisn'     => $s->nisn,
            'status'   => $existing[$s->id] ?? null,
        ]);

        return response()->json([
            'tanggal'      => $tanggal,
            'sudah_diisi'  => $existing->isNotEmpty(),
            'siswa'        => $data,
        ]);
    }

    public function store(Request $request, Jadwal $jadwal)
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $request->validate([
            'tanggal'        => 'required|date',
            'absensi'        => 'required|array',
            'absensi.*.siswa_id'   => 'required|integer|exists:siswas,id',
            'absensi.*.status'     => 'required|in:Hadir,Izin,Sakit,Alpa',
            'absensi.*.keterangan' => 'nullable|string|max:200',
        ]);

        foreach ($request->absensi as $item) {
            Absensi::updateOrCreate(
                ['jadwal_id' => $jadwal->id, 'siswa_id' => $item['siswa_id'], 'tanggal' => $request->tanggal],
                [
                    'status'       => $item['status'],
                    'keterangan'   => $item['keterangan'] ?? null,
                    'dicatat_oleh' => auth()->id(),
                ]
            );
        }

        return response()->json(['message' => 'Absensi berhasil disimpan.']);
    }
}
