<?php

namespace App\Http\Controllers\Siswa;

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
        $siswa = Siswa::where('user_id', auth()->id())->first();

        $stats = null;
        if ($penempatan) {
            $stats = [
                'total_jurnal'  => $penempatan->jurnals()->count(),
                'total_absensi' => $penempatan->absensis()->count(),
                'nilai'         => $penempatan->nilai,
            ];
        }

        return view('siswa.pkl.index', compact('penempatan', 'siswa', 'stats'));
    }

    public function jurnal()
    {
        $penempatan = $this->getPenempatan();
        if (!$penempatan) return redirect()->route('siswa.pkl')->with('error', 'Anda belum memiliki penempatan PKL aktif.');

        $jurnals = $penempatan->jurnals()->orderBy('tanggal', 'desc')->get();
        return view('siswa.pkl.jurnal', compact('penempatan', 'jurnals'));
    }

    public function jurnalStore(Request $request)
    {
        $penempatan = $this->getPenempatan();
        if (!$penempatan) return redirect()->route('siswa.pkl')->with('error', 'Tidak ada penempatan PKL aktif.');

        $request->validate([
            'tanggal'     => 'required|date|unique:jurnal_pkls,tanggal,NULL,id,penempatan_pkl_id,' . $penempatan->id,
            'kegiatan'    => 'required|string',
            'keterangan'  => 'nullable|string|max:500',
            'foto'        => 'nullable|image|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('pkl/jurnal', 'public');
        }

        JurnalPkl::create([
            'penempatan_pkl_id' => $penempatan->id,
            'tanggal'           => $request->tanggal,
            'kegiatan'          => $request->kegiatan,
            'keterangan'        => $request->keterangan,
            'foto'              => $fotoPath,
        ]);

        return redirect()->route('siswa.pkl.jurnal')->with('success', 'Jurnal berhasil ditambahkan.');
    }

    public function absensi()
    {
        $penempatan = $this->getPenempatan();
        if (!$penempatan) return redirect()->route('siswa.pkl')->with('error', 'Anda belum memiliki penempatan PKL aktif.');

        $hari_ini = today()->toDateString();
        $absensiHariIni = AbsensiPkl::where('penempatan_pkl_id', $penempatan->id)
            ->where('tanggal', $hari_ini)
            ->first();

        $riwayat = AbsensiPkl::where('penempatan_pkl_id', $penempatan->id)
            ->orderBy('tanggal', 'desc')
            ->take(30)
            ->get();

        return view('siswa.pkl.absensi', compact('penempatan', 'absensiHariIni', 'riwayat', 'hari_ini'));
    }

    public function absensiMasuk(Request $request)
    {
        $penempatan = $this->getPenempatan();
        if (!$penempatan) return response()->json(['error' => 'Tidak ada penempatan PKL aktif.'], 422);

        $request->validate([
            'tanggal'   => 'required|date',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto'      => 'required|string', // base64
        ]);

        if (AbsensiPkl::where('penempatan_pkl_id', $penempatan->id)->where('tanggal', $request->tanggal)->exists()) {
            return response()->json(['error' => 'Sudah absen masuk hari ini.'], 422);
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

        return response()->json(['success' => true, 'jam' => now()->format('H:i')]);
    }

    public function absensiPulang(Request $request)
    {
        $penempatan = $this->getPenempatan();
        if (!$penempatan) return response()->json(['error' => 'Tidak ada penempatan PKL aktif.'], 422);

        $request->validate([
            'tanggal'   => 'required|date',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto'      => 'required|string',
        ]);

        $absensi = AbsensiPkl::where('penempatan_pkl_id', $penempatan->id)
            ->where('tanggal', $request->tanggal)
            ->firstOrFail();

        if ($absensi->jam_pulang) {
            return response()->json(['error' => 'Sudah absen pulang hari ini.'], 422);
        }

        $fotoPath = $this->saveBase64Foto($request->foto, 'pkl/absensi');

        $absensi->update([
            'jam_pulang'  => now()->format('H:i:s'),
            'foto_pulang' => $fotoPath,
        ]);

        return response()->json(['success' => true, 'jam' => now()->format('H:i')]);
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
