<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\NilaiPkl;
use App\Models\PenempatanPkl;
use App\Models\Semester;
use Illuminate\Http\Request;

class PklController extends Controller
{
    public function index()
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        $semester = Semester::aktif();

        $bimbingans = PenempatanPkl::with(['siswa.kelas', 'tempatPkl', 'nilai'])
            ->where('guru_pembimbing_id', $guru->id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->get();

        return view('guru.pkl.index', compact('bimbingans', 'guru'));
    }

    public function detail(PenempatanPkl $penempatan)
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($penempatan->guru_pembimbing_id !== $guru->id, 403);

        $penempatan->load(['siswa.kelas', 'tempatPkl', 'jurnals', 'absensis', 'nilai']);
        return view('guru.pkl.detail', compact('penempatan'));
    }

    public function nilaiForm(PenempatanPkl $penempatan)
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($penempatan->guru_pembimbing_id !== $guru->id, 403);

        $penempatan->load(['siswa', 'tempatPkl', 'nilai']);
        return view('guru.pkl.nilai', compact('penempatan'));
    }

    public function nilaiSimpan(Request $request, PenempatanPkl $penempatan)
    {
        $guru = Guru::where('user_id', auth()->id())->firstOrFail();
        abort_if($penempatan->guru_pembimbing_id !== $guru->id, 403);

        $request->validate([
            'nilai_pembimbing' => 'nullable|numeric|min:0|max:100',
            'nilai_du_di'      => 'nullable|numeric|min:0|max:100',
            'nilai_sikap'      => 'nullable|numeric|min:0|max:100',
            'catatan'          => 'nullable|string|max:1000',
        ]);

        NilaiPkl::updateOrCreate(
            ['penempatan_pkl_id' => $penempatan->id],
            [
                'nilai_pembimbing' => $request->nilai_pembimbing,
                'nilai_du_di'      => $request->nilai_du_di,
                'nilai_sikap'      => $request->nilai_sikap,
                'catatan'          => $request->catatan,
                'dicatat_oleh'     => auth()->id(),
            ]
        );

        return redirect()->route('guru.pkl.detail', $penempatan)->with('success', 'Nilai PKL berhasil disimpan.');
    }
}
