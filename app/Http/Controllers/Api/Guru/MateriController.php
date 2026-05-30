<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Http\Resources\MateriResource;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Materi;
use Illuminate\Http\Request;

class MateriController extends Controller
{
    private function getGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * Materi pada satu jadwal milik guru (termasuk yang belum terbit).
     */
    public function show(Jadwal $jadwal)
    {
        $guru = $this->getGuru();
        abort_if($jadwal->guru_id !== $guru->id, 403, 'Akses ditolak.');

        $materis = Materi::with(['jadwal.mapel', 'guru'])
            ->where('jadwal_id', $jadwal->id)
            ->orderByDesc('created_at')
            ->get();

        return MateriResource::collection($materis);
    }

    public function store(Request $request, Jadwal $jadwal)
    {
        $guru = $this->getGuru();
        abort_if($jadwal->guru_id !== $guru->id, 403, 'Akses ditolak.');

        $request->validate([
            'judul'        => 'required|string|max:255',
            'deskripsi'    => 'nullable|string',
            'tipe'         => 'required|in:file,link,teks',
            'file'         => 'required_if:tipe,file|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,jpg,jpeg,png|max:10240',
            'link'         => 'required_if:tipe,link|nullable|url|max:500',
            'teks'         => 'required_if:tipe,teks|nullable|string',
            'is_published' => 'boolean',
        ]);

        $data = [
            'jadwal_id'    => $jadwal->id,
            'guru_id'      => $guru->id,
            'judul'        => $request->judul,
            'deskripsi'    => $request->deskripsi,
            'tipe'         => $request->tipe,
            'is_published' => $request->boolean('is_published', true),
        ];

        if ($request->tipe === 'file') {
            $file = $request->file('file');
            $data['konten']    = $file->store('elearning/materi', 'public');
            $data['file_name'] = $file->getClientOriginalName();
        } elseif ($request->tipe === 'link') {
            $data['konten'] = $request->link;
        } else {
            $data['konten'] = $request->teks;
        }

        $materi = Materi::create($data);
        $materi->load(['jadwal.mapel', 'guru']);

        return (new MateriResource($materi))
            ->additional(['message' => 'Materi berhasil ditambahkan.'])
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Materi $materi)
    {
        $guru = $this->getGuru();
        abort_if($materi->guru_id !== $guru->id, 403, 'Akses ditolak.');

        if ($materi->tipe === 'file' && $materi->konten) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($materi->konten);
        }
        $materi->delete();

        return response()->json(['message' => 'Materi berhasil dihapus.']);
    }
}
