<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendWaNotification;
use App\Models\Kelas;
use App\Models\Pengumuman;
use App\Models\Siswa;
use App\Services\WaTemplates;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index()
    {
        $pengumumans = Pengumuman::with(['pembuat', 'targetKelas'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pengumuman.index', compact('pengumumans'));
    }

    public function create()
    {
        $kelas       = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $targetRoles = ['' => 'Semua Pengguna', '1' => 'Guru', '2' => 'Siswa'];

        return view('admin.pengumuman.form', [
            'pengumuman'  => null,
            'kelas'       => $kelas,
            'targetRoles' => $targetRoles,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'            => 'required|string|max:255',
            'isi'              => 'required|string',
            'target_role'      => 'nullable|in:0,1,2',
            'target_kelas_id'  => 'nullable|exists:kelas,id',
            'published_at'     => 'nullable|date',
        ]);

        $pengumuman = Pengumuman::create([
            'judul'           => $request->judul,
            'isi'             => clean($request->isi),
            'target_role'     => $request->target_role !== '' ? $request->target_role : null,
            'target_kelas_id' => $request->target_kelas_id,
            'dibuat_oleh'     => auth()->id(),
            'published_at'    => $request->published_at,
        ]);

        // Kirim notifikasi jika langsung publish
        if ($pengumuman->is_published) {
            $pengumuman->broadcastNotifikasi();
        }

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil disimpan.');
    }

    public function edit(Pengumuman $pengumuman)
    {
        $kelas       = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $targetRoles = ['' => 'Semua Pengguna', '1' => 'Guru', '2' => 'Siswa'];

        return view('admin.pengumuman.form', compact('pengumuman', 'kelas', 'targetRoles'));
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $request->validate([
            'judul'           => 'required|string|max:255',
            'isi'             => 'required|string',
            'target_role'     => 'nullable|in:0,1,2',
            'target_kelas_id' => 'nullable|exists:kelas,id',
            'published_at'    => 'nullable|date',
        ]);

        $wasPublished = $pengumuman->is_published;

        $pengumuman->update([
            'judul'           => $request->judul,
            'isi'             => clean($request->isi),
            'target_role'     => $request->target_role !== '' ? $request->target_role : null,
            'target_kelas_id' => $request->target_kelas_id,
            'published_at'    => $request->published_at,
        ]);

        // Broadcast notifikasi hanya jika baru di-publish (sebelumnya belum)
        if (!$wasPublished && $pengumuman->fresh()->is_published) {
            $pengumuman->broadcastNotifikasi();
        }

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return redirect()->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }

    public function show(Pengumuman $pengumuman)
    {
        return view('admin.pengumuman.show', compact('pengumuman'));
    }

    /** Publish sekarang (tanpa tanggal tertentu) */
    public function publish(Pengumuman $pengumuman)
    {
        $wasPublished = $pengumuman->is_published;
        $pengumuman->update(['published_at' => now()]);

        if (!$wasPublished) {
            $pengumuman->broadcastNotifikasi();

            // Broadcast WA ke siswa yang punya no_wa
            $ringkasan = \Str::limit(strip_tags($pengumuman->isi), 200);
            $waMessage = WaTemplates::pengumumanBaru($pengumuman->judul, $ringkasan);

            $siswaQuery = Siswa::query();
            if ($pengumuman->target_kelas_id) {
                $siswaQuery->where('kelas_id', $pengumuman->target_kelas_id);
            }
            $siswaQuery->whereNotNull('no_wa')->each(function ($siswa) use ($waMessage) {
                SendWaNotification::dispatch($siswa->no_wa, $waMessage, 'pengumuman');
            });
        }

        return back()->with('success', 'Pengumuman berhasil dipublish.');
    }
}
