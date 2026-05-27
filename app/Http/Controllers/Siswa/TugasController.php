<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\PengumpulanTugas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    private function getSiswa(): Siswa
    {
        return Siswa::where('user_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $siswa    = $this->getSiswa();
        $semester = Semester::aktif();

        $jadwalIds = Jadwal::where('kelas_id', $siswa->kelas_id)
            ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
            ->pluck('id');

        $tugas = Tugas::with(['jadwal.mapel'])
            ->whereIn('jadwal_id', $jadwalIds)
            ->orderBy('deadline')
            ->get();

        // Ambil semua pengumpulan siswa ini sekaligus
        $pengumpulans = PengumpulanTugas::where('siswa_id', $siswa->id)
            ->whereIn('tugas_id', $tugas->pluck('id'))
            ->get()
            ->keyBy('tugas_id');

        return view('siswa.tugas.index', compact('tugas', 'pengumpulans', 'semester'));
    }

    public function show(Tugas $tugas)
    {
        $siswa = $this->getSiswa();
        abort_if($tugas->jadwal->kelas_id !== $siswa->kelas_id, 403);

        $tugas->load(['jadwal.mapel', 'jadwal.guru']);
        $pengumpulan = PengumpulanTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        return view('siswa.tugas.show', compact('tugas', 'pengumpulan', 'siswa'));
    }

    public function kumpul(Request $request, Tugas $tugas)
    {
        $siswa = $this->getSiswa();
        abort_if($tugas->jadwal->kelas_id !== $siswa->kelas_id, 403);
        abort_if($tugas->is_expired, 422, 'Deadline tugas sudah lewat.');

        $sudahKumpul = PengumpulanTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $siswa->id)
            ->exists();
        abort_if($sudahKumpul, 422, 'Kamu sudah mengumpulkan tugas ini.');

        $rules = ['catatan' => 'nullable|string|max:1000'];
        if ($tugas->tipe_pengumpulan === 'file') {
            $rules['file'] = 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,jpg,jpeg,png|max:10240';
        } elseif ($tugas->tipe_pengumpulan === 'link') {
            $rules['link'] = 'required|url|max:500';
        } else {
            $rules['teks'] = 'required|string';
        }
        $request->validate($rules);

        $data = [
            'tugas_id' => $tugas->id,
            'siswa_id' => $siswa->id,
            'catatan'  => $request->catatan,
        ];

        if ($tugas->tipe_pengumpulan === 'file') {
            $file = $request->file('file');
            $data['konten']    = $file->store('elearning/tugas', 'public');
            $data['file_name'] = $file->getClientOriginalName();
        } elseif ($tugas->tipe_pengumpulan === 'link') {
            $data['konten'] = $request->link;
        } else {
            $data['konten'] = $request->teks;
        }

        PengumpulanTugas::create($data);

        return redirect()->route('siswa.tugas.show', $tugas)
            ->with('success', 'Tugas berhasil dikumpulkan!');
    }

    public function hapusKumpulan(PengumpulanTugas $pengumpulan)
    {
        $siswa = $this->getSiswa();
        abort_if($pengumpulan->siswa_id !== $siswa->id, 403);
        abort_if($pengumpulan->is_dinilai, 422, 'Pengumpulan yang sudah dinilai tidak bisa dihapus.');

        if ($pengumpulan->tugas->tipe_pengumpulan === 'file' && $pengumpulan->konten) {
            Storage::disk('public')->delete($pengumpulan->konten);
        }

        $tugasId = $pengumpulan->tugas_id;
        $pengumpulan->delete();

        return redirect()->route('siswa.tugas.show', $tugasId)
            ->with('success', 'Pengumpulan berhasil dihapus. Kamu bisa kumpul ulang sebelum deadline.');
    }
}
