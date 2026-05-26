<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AbsensiEkskul;
use App\Models\AnggotaEkskul;
use App\Models\Ekskul;
use App\Models\Guru;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class EkskulController extends Controller
{
    private function getGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $guru = $this->getGuru();
        $ekskuls = Ekskul::with('anggotas')
            ->where('pembina_id', $guru->id)
            ->orderBy('nama')
            ->get();

        return view('guru.ekskul.index', compact('ekskuls', 'guru'));
    }

    public function detail(Ekskul $ekskul)
    {
        $guru = $this->getGuru();
        abort_if($ekskul->pembina_id !== $guru->id, 403, 'Anda bukan pembina ekskul ini.');

        $tahunAjaran = TahunAjaran::aktif();
        $anggotas = AnggotaEkskul::with('siswa')
            ->where('ekskul_id', $ekskul->id)
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->get();

        return view('guru.ekskul.detail', compact('ekskul', 'anggotas', 'tahunAjaran'));
    }

    public function absensiForm(Ekskul $ekskul, Request $request)
    {
        $guru = $this->getGuru();
        abort_if($ekskul->pembina_id !== $guru->id, 403, 'Anda bukan pembina ekskul ini.');

        $tanggal = $request->tanggal ?? today()->toDateString();
        $tahunAjaran = TahunAjaran::aktif();

        $anggotas = AnggotaEkskul::with('siswa')
            ->where('ekskul_id', $ekskul->id)
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->get();

        $existingAbsensi = AbsensiEkskul::where('ekskul_id', $ekskul->id)
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        return view('guru.ekskul.absensi', compact('ekskul', 'anggotas', 'tanggal', 'existingAbsensi'));
    }

    public function absensiStore(Ekskul $ekskul, Request $request)
    {
        $guru = $this->getGuru();
        abort_if($ekskul->pembina_id !== $guru->id, 403, 'Anda bukan pembina ekskul ini.');

        $request->validate([
            'tanggal'        => 'required|date',
            'absensi'        => 'required|array',
            'absensi.*.status' => 'required|in:Hadir,Izin,Sakit,Alpa',
        ]);

        foreach ($request->absensi as $siswaId => $data) {
            AbsensiEkskul::updateOrCreate(
                [
                    'ekskul_id' => $ekskul->id,
                    'siswa_id'  => $siswaId,
                    'tanggal'   => $request->tanggal,
                ],
                [
                    'status'       => $data['status'],
                    'keterangan'   => $data['keterangan'] ?? null,
                    'dicatat_oleh' => auth()->id(),
                ]
            );
        }

        return redirect()->route('guru.ekskul.rekap', $ekskul)
            ->with('success', 'Absensi ekskul tanggal ' . $request->tanggal . ' berhasil disimpan.');
    }

    public function absensiRekap(Ekskul $ekskul)
    {
        $guru = $this->getGuru();
        abort_if($ekskul->pembina_id !== $guru->id, 403, 'Anda bukan pembina ekskul ini.');

        $tahunAjaran = TahunAjaran::aktif();

        $anggotas = AnggotaEkskul::with('siswa')
            ->where('ekskul_id', $ekskul->id)
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
            ->get();

        // Semua tanggal pertemuan unik ekskul ini
        $tanggals = AbsensiEkskul::where('ekskul_id', $ekskul->id)
            ->orderBy('tanggal')
            ->groupBy('tanggal')
            ->pluck('tanggal');

        // Build grid: siswa_id -> [tanggal -> AbsensiEkskul]
        $grid = [];
        foreach ($anggotas as $anggota) {
            $absensiSiswa = AbsensiEkskul::where('ekskul_id', $ekskul->id)
                ->where('siswa_id', $anggota->siswa_id)
                ->get()
                ->keyBy('tanggal');

            $jumlahHadir = $absensiSiswa->where('status', 'Hadir')->count();
            $grid[$anggota->siswa_id] = [
                'siswa'        => $anggota->siswa,
                'absensi'      => $absensiSiswa,
                'jumlah_hadir' => $jumlahHadir,
                'total'        => $tanggals->count(),
                'persen_hadir' => $tanggals->count() > 0 ? round(($jumlahHadir / $tanggals->count()) * 100) : 0,
            ];
        }

        return view('guru.ekskul.rekap', compact('ekskul', 'tanggals', 'grid'));
    }
}
