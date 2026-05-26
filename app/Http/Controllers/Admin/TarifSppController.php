<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\TarifSpp;
use Illuminate\Http\Request;

class TarifSppController extends Controller
{
    public function index(Request $request)
    {
        $query = TarifSpp::with(['kelas.jurusan', 'tahunAjaran'])
            ->orderBy('tahun_ajaran_id', 'desc');

        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        $tarifs       = $query->get();
        $tahunAjarans = TahunAjaran::orderBy('nama', 'desc')->get();

        return view('admin.keuangan.tarif.index', compact('tarifs', 'tahunAjarans'));
    }

    public function create()
    {
        $kelas        = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $tahunAjarans = TahunAjaran::orderBy('nama', 'desc')->get();
        return view('admin.keuangan.tarif.form', [
            'tarif'       => null,
            'kelas'       => $kelas,
            'tahunAjarans' => $tahunAjarans,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id'        => 'required|exists:kelas,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'nominal'         => 'required|numeric|min:1000',
        ]);

        // Cek duplikat
        $exists = TarifSpp::where('kelas_id', $request->kelas_id)
            ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['kelas_id' => 'Tarif untuk kelas dan tahun ajaran ini sudah ada.'])->withInput();
        }

        TarifSpp::create($request->only(['kelas_id', 'tahun_ajaran_id', 'nominal']));

        return redirect()->route('admin.keuangan.tarif.index')
            ->with('success', 'Tarif SPP berhasil ditambahkan.');
    }

    public function edit(TarifSpp $tarif)
    {
        $kelas        = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $tahunAjarans = TahunAjaran::orderBy('nama', 'desc')->get();
        return view('admin.keuangan.tarif.form', compact('tarif', 'kelas', 'tahunAjarans'));
    }

    public function update(Request $request, TarifSpp $tarif)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1000',
        ]);

        $tarif->update(['nominal' => $request->nominal]);

        return redirect()->route('admin.keuangan.tarif.index')
            ->with('success', 'Tarif SPP berhasil diperbarui.');
    }

    public function destroy(TarifSpp $tarif)
    {
        $tarif->delete();
        return redirect()->route('admin.keuangan.tarif.index')
            ->with('success', 'Tarif SPP berhasil dihapus.');
    }
}
