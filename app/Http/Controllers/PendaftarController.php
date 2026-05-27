<?php

namespace App\Http\Controllers;

use App\Models\PendaftarPpdb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PendaftarController extends Controller
{
    /**
     * Dashboard pendaftar — status, berkas, info pendaftaran.
     */
    public function dashboard()
    {
        $pendaftar = auth()->user()->pendaftarPpdb()
            ->with(['jurusanPilihan', 'jurusanPilihan2', 'kelasDiterima', 'gelombangPpdb'])
            ->firstOrFail();

        $dokumenFields  = PendaftarPpdb::DOKUMEN_FIELDS;
        $totalDokumen   = count($dokumenFields);
        $uploadedCount  = collect(array_keys($dokumenFields))->filter(fn($f) => !empty($pendaftar->$f))->count();

        return view('pendaftar.dashboard', compact('pendaftar', 'dokumenFields', 'totalDokumen', 'uploadedCount'));
    }

    /**
     * Upload satu berkas dokumen.
     * Route: POST /pendaftar/dokumen/{field}
     */
    public function uploadDokumen(Request $request, string $field)
    {
        if (!array_key_exists($field, PendaftarPpdb::DOKUMEN_FIELDS)) {
            abort(404);
        }

        $isFoto = $field === 'foto';

        $request->validate([
            'file' => $isFoto
                ? 'required|image|mimes:jpg,jpeg,png|max:1024'
                : 'required|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ], [
            'file.required' => 'Pilih file terlebih dahulu.',
            'file.max'      => $isFoto ? 'Foto maksimal 1 MB.' : 'File maksimal 4 MB.',
            'file.mimes'    => $isFoto ? 'Format foto harus JPG atau PNG.' : 'Format file harus PDF, JPG, atau PNG.',
        ]);

        $pendaftar = auth()->user()->pendaftarPpdb;

        // Hapus file lama jika ada
        if ($pendaftar->$field) {
            Storage::disk('public')->delete($pendaftar->$field);
        }

        $path = $request->file('file')->store('ppdb/' . $field, 'public');
        $pendaftar->update([$field => $path]);

        return back()->with('success', PendaftarPpdb::DOKUMEN_FIELDS[$field] . ' berhasil diupload.');
    }

    /**
     * Hapus satu berkas dokumen.
     * Route: DELETE /pendaftar/dokumen/{field}
     */
    public function hapusDokumen(string $field)
    {
        if (!array_key_exists($field, PendaftarPpdb::DOKUMEN_FIELDS)) {
            abort(404);
        }

        $pendaftar = auth()->user()->pendaftarPpdb;

        if ($pendaftar->$field) {
            Storage::disk('public')->delete($pendaftar->$field);
            $pendaftar->update([$field => null]);
        }

        return back()->with('success', PendaftarPpdb::DOKUMEN_FIELDS[$field] . ' berhasil dihapus.');
    }

    /**
     * Cetak bukti pendaftaran (print-friendly, tanpa layout).
     */
    public function cetakBukti()
    {
        $pendaftar = auth()->user()->pendaftarPpdb()
            ->with(['jurusanPilihan', 'jurusanPilihan2', 'gelombangPpdb'])
            ->firstOrFail();

        return view('ppdb.cetak', compact('pendaftar'));
    }
}
