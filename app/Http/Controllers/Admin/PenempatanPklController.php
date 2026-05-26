<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\PenempatanPkl;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TempatPkl;
use Illuminate\Http\Request;

class PenempatanPklController extends Controller
{
    public function index()
    {
        $penempatans = PenempatanPkl::with(['siswa', 'tempatPkl', 'guruPembimbing', 'semester'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.pkl.penempatan.index', compact('penempatans'));
    }

    public function create()
    {
        $siswas  = Siswa::orderBy('nama')->get();
        $tempats = TempatPkl::orderBy('nama')->get();
        $gurus   = Guru::orderBy('nama')->get();
        $semesters = Semester::with('tahunAjaran')->orderBy('id', 'desc')->get();
        return view('admin.pkl.penempatan.form', compact('siswas', 'tempats', 'gurus', 'semesters') + ['penempatan' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id'           => 'required|exists:siswas,id',
            'tempat_pkl_id'      => 'required|exists:tempat_pkls,id',
            'guru_pembimbing_id' => 'nullable|exists:gurus,id',
            'semester_id'        => 'required|exists:semesters,id',
            'periode_mulai'      => 'nullable|date',
            'periode_selesai'    => 'nullable|date|after_or_equal:periode_mulai',
            'status'             => 'required|in:aktif,selesai',
        ]);

        PenempatanPkl::create($request->only(
            'siswa_id', 'tempat_pkl_id', 'guru_pembimbing_id', 'semester_id',
            'periode_mulai', 'periode_selesai', 'status'
        ));

        return redirect()->route('admin.pkl-penempatan.index')->with('success', 'Penempatan PKL berhasil disimpan.');
    }

    public function edit(PenempatanPkl $pklPenempatan)
    {
        $siswas    = Siswa::orderBy('nama')->get();
        $tempats   = TempatPkl::orderBy('nama')->get();
        $gurus     = Guru::orderBy('nama')->get();
        $semesters = Semester::with('tahunAjaran')->orderBy('id', 'desc')->get();
        return view('admin.pkl.penempatan.form', compact('siswas', 'tempats', 'gurus', 'semesters') + ['penempatan' => $pklPenempatan]);
    }

    public function update(Request $request, PenempatanPkl $pklPenempatan)
    {
        $request->validate([
            'siswa_id'           => 'required|exists:siswas,id',
            'tempat_pkl_id'      => 'required|exists:tempat_pkls,id',
            'guru_pembimbing_id' => 'nullable|exists:gurus,id',
            'semester_id'        => 'required|exists:semesters,id',
            'periode_mulai'      => 'nullable|date',
            'periode_selesai'    => 'nullable|date|after_or_equal:periode_mulai',
            'status'             => 'required|in:aktif,selesai',
        ]);
        $pklPenempatan->update($request->only(
            'siswa_id', 'tempat_pkl_id', 'guru_pembimbing_id', 'semester_id',
            'periode_mulai', 'periode_selesai', 'status'
        ));
        return redirect()->route('admin.pkl-penempatan.index')->with('success', 'Penempatan PKL berhasil diperbarui.');
    }

    public function destroy(PenempatanPkl $pklPenempatan)
    {
        $pklPenempatan->delete();
        return redirect()->route('admin.pkl-penempatan.index')->with('success', 'Penempatan PKL berhasil dihapus.');
    }
}
