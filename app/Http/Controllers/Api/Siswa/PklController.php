<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AbsensiPkl;
use App\Models\JurnalPkl;
use App\Models\PenempatanPkl;
use App\Models\Semester;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PklController extends Controller
{
    private function getPenempatan(): ?PenempatanPkl
    {
        $siswa = Siswa::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        return PenempatanPkl::with(['tempatPkl', 'guruPembimbing', 'semester.tahunAjaran'])
            ->where('siswa_id', $siswa->id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->latest()
            ->first();
    }

    public function index()
    {
        $penempatan = $this->getPenempatan();

        if (! $penempatan) {
            return response()->json(['message' => 'Belum ada penempatan PKL aktif.', 'data' => null]);
        }

        $penempatan->load(['jurnals', 'absensis', 'nilai']);

        return response()->json([
            'data' => [
                'id'              => $penempatan->id,
                'tempat'          => $penempatan->tempatPkl?->nama ?? null,
                'alamat'          => $penempatan->tempatPkl?->alamat ?? null,
                'guru_pembimbing' => $penempatan->guruPembimbing?->nama ?? null,
                'periode_mulai'   => $penempatan->periode_mulai?->toDateString(),
                'periode_selesai' => $penempatan->periode_selesai?->toDateString(),
                'status'          => $penempatan->status,
                'total_jurnal'    => $penempatan->jurnals->count(),
                'total_absensi'   => $penempatan->absensis->count(),
                'nilai'           => $penempatan->nilai ? [
                    'pembimbing' => $penempatan->nilai->nilai_pembimbing,
                    'du_di'      => $penempatan->nilai->nilai_du_di,
                    'sikap'      => $penempatan->nilai->nilai_sikap,
                ] : null,
                'jurnals' => $penempatan->jurnals->sortByDesc('tanggal')->values()->map(fn($j) => [
                    'id'         => $j->id,
                    'tanggal'    => $j->tanggal?->toDateString(),
                    'kegiatan'   => $j->kegiatan,
                    'keterangan' => $j->keterangan,
                ]),
            ],
        ]);
    }

    public function storeJurnal(Request $request)
    {
        $penempatan = $this->getPenempatan();
        if (! $penempatan) {
            return response()->json(['message' => 'Tidak ada penempatan PKL aktif.'], 422);
        }

        $request->validate([
            'tanggal'    => 'required|date|unique:jurnal_pkls,tanggal,NULL,id,penempatan_pkl_id,' . $penempatan->id,
            'kegiatan'   => 'required|string',
            'keterangan' => 'nullable|string|max:500',
            'foto'       => 'nullable|image|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('pkl/jurnal', 'public');
        }

        $jurnal = JurnalPkl::create([
            'penempatan_pkl_id' => $penempatan->id,
            'tanggal'           => $request->tanggal,
            'kegiatan'          => $request->kegiatan,
            'keterangan'        => $request->keterangan,
            'foto'              => $fotoPath,
        ]);

        return response()->json(['message' => 'Jurnal berhasil ditambahkan.', 'data' => $jurnal], 201);
    }

    public function absensiMasuk(Request $request)
    {
        $penempatan = $this->getPenempatan();
        if (! $penempatan) {
            return response()->json(['message' => 'Tidak ada penempatan PKL aktif.'], 422);
        }

        $request->validate([
            'tanggal'   => 'required|date',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto'      => 'required|string',
        ]);

        if (AbsensiPkl::where('penempatan_pkl_id', $penempatan->id)->where('tanggal', $request->tanggal)->exists()) {
            return response()->json(['message' => 'Sudah absen masuk hari ini.'], 422);
        }

        $fotoPath = $this->saveBase64Foto($request->foto, 'pkl/absensi');

        AbsensiPkl::create([
            'penempatan_pkl_id' => $penempatan->id,
            'tanggal'           => $request->tanggal,
            'jam_masuk'         => now()->format('H:i:s'),
            'latitude'          => $request->latitude,
            'longitude'         => $request->longitude,
            'foto_masuk'        => $fotoPath,
            'keterangan'        => $request->keterangan,
        ]);

        return response()->json(['message' => 'Check-in berhasil.', 'jam' => now()->format('H:i')], 201);
    }

    public function absensiPulang(Request $request)
    {
        $penempatan = $this->getPenempatan();
        if (! $penempatan) {
            return response()->json(['message' => 'Tidak ada penempatan PKL aktif.'], 422);
        }

        $request->validate([
            'tanggal'   => 'required|date',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto'      => 'required|string',
        ]);

        $absensi = AbsensiPkl::where('penempatan_pkl_id', $penempatan->id)
            ->where('tanggal', $request->tanggal)
            ->first();

        if (! $absensi) {
            return response()->json(['message' => 'Belum absen masuk hari ini.'], 422);
        }
        if ($absensi->jam_pulang) {
            return response()->json(['message' => 'Sudah absen pulang hari ini.'], 422);
        }

        $fotoPath = $this->saveBase64Foto($request->foto, 'pkl/absensi');

        $absensi->update([
            'jam_pulang'  => now()->format('H:i:s'),
            'foto_pulang' => $fotoPath,
        ]);

        return response()->json(['message' => 'Check-out berhasil.', 'jam' => now()->format('H:i')]);
    }

    private function saveBase64Foto(string $base64, string $dir): string
    {
        $data = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $content = base64_decode($data);
        $filename = $dir . '/' . uniqid() . '_' . now()->format('Ymd_His') . '.jpg';
        Storage::disk('public')->put($filename, $content);
        return $filename;
    }
}
