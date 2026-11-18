<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Materi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MateriController extends Controller
{
    private function getGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $guru = $this->getGuru();

        $jadwals = Jadwal::with(['mapel', 'kelas', 'semester.tahunAjaran'])
            ->where('guru_id', $guru->id)
            ->withCount('materis')
            ->urutHari()
            ->orderBy('jam_mulai')
            ->get();

        return view('guru.materi.index', compact('jadwals'));
    }

    public function show(Jadwal $jadwal)
    {
        $guru = $this->getGuru();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $jadwal->load(['mapel', 'kelas', 'semester.tahunAjaran']);
        $materis = Materi::where('jadwal_id', $jadwal->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('guru.materi.show', compact('jadwal', 'materis'));
    }

    public function create(Jadwal $jadwal)
    {
        $guru = $this->getGuru();
        abort_if($jadwal->guru_id !== $guru->id, 403);

        $jadwal->load(['mapel', 'kelas']);
        return view('guru.materi.form', compact('jadwal'));
    }

    public function store(Request $request, Jadwal $jadwal)
    {
        $guru = $this->getGuru();
        abort_if($jadwal->guru_id !== $guru->id, 403);

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
            $path = $file->store('elearning/materi', 'public');
            $data['konten']    = $path;
            $data['file_name'] = $file->getClientOriginalName();
        } elseif ($request->tipe === 'link') {
            $data['konten'] = $request->link;
        } else {
            $data['konten'] = $request->teks;
        }

        Materi::create($data);

        return redirect()->route('guru.materi.show', $jadwal)
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function edit(Materi $materi)
    {
        $guru = $this->getGuru();
        abort_if($materi->guru_id !== $guru->id, 403);

        $materi->load(['jadwal.mapel', 'jadwal.kelas']);
        return view('guru.materi.form', ['jadwal' => $materi->jadwal, 'materi' => $materi]);
    }

    public function update(Request $request, Materi $materi)
    {
        $guru = $this->getGuru();
        abort_if($materi->guru_id !== $guru->id, 403);

        $request->validate([
            'judul'        => 'required|string|max:255',
            'deskripsi'    => 'nullable|string',
            'tipe'         => 'required|in:file,link,teks',
            'file'         => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,jpg,jpeg,png|max:10240',
            'link'         => 'required_if:tipe,link|nullable|url|max:500',
            'teks'         => 'required_if:tipe,teks|nullable|string',
            'is_published' => 'boolean',
        ]);

        $data = [
            'judul'        => $request->judul,
            'deskripsi'    => $request->deskripsi,
            'tipe'         => $request->tipe,
            'is_published' => $request->boolean('is_published', true),
        ];

        if ($request->tipe === 'file') {
            if ($request->hasFile('file')) {
                if ($materi->tipe === 'file' && $materi->konten) {
                    Storage::disk('public')->delete($materi->konten);
                }
                $file = $request->file('file');
                $data['konten']    = $file->store('elearning/materi', 'public');
                $data['file_name'] = $file->getClientOriginalName();
            }
        } elseif ($request->tipe === 'link') {
            $data['konten']    = $request->link;
            $data['file_name'] = null;
        } else {
            $data['konten']    = $request->teks;
            $data['file_name'] = null;
        }

        $materi->update($data);

        return redirect()->route('guru.materi.show', $materi->jadwal_id)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Materi $materi)
    {
        $guru = $this->getGuru();
        abort_if($materi->guru_id !== $guru->id, 403);

        if ($materi->tipe === 'file' && $materi->konten) {
            Storage::disk('public')->delete($materi->konten);
        }

        $jadwalId = $materi->jadwal_id;
        $materi->delete();

        return redirect()->route('guru.materi.show', $jadwalId)
            ->with('success', 'Materi berhasil dihapus.');
    }
}
