<?php

namespace App\Http\Controllers\Api\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Resources\PengumpulanTugasResource;
use App\Http\Resources\TugasResource;
use App\Models\Jadwal;
use App\Models\PengumpulanTugas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Tugas;
use Illuminate\Http\Request;

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
            ->when($semester, fn ($q) => $q->where('semester_id', $semester->id))
            ->pluck('id');

        $tugas = Tugas::with(['jadwal.mapel'])
            ->whereIn('jadwal_id', $jadwalIds)
            ->orderBy('deadline')
            ->get();

        $pengumpulans = PengumpulanTugas::where('siswa_id', $siswa->id)
            ->whereIn('tugas_id', $tugas->pluck('id'))
            ->get()
            ->keyBy('tugas_id');

        // Sematkan pengumpulan siswa ke tiap tugas agar terserialisasi resource.
        $tugas->each(fn ($t) => $t->pengumpulanku = $pengumpulans->get($t->id));

        return TugasResource::collection($tugas);
    }

    public function show(Tugas $tugas)
    {
        $siswa = $this->getSiswa();
        abort_if($tugas->jadwal->kelas_id !== $siswa->kelas_id, 403, 'Akses ditolak.');

        $tugas->load(['jadwal.mapel', 'jadwal.guru']);
        $tugas->pengumpulanku = PengumpulanTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        return new TugasResource($tugas);
    }

    public function kumpul(Request $request, Tugas $tugas)
    {
        $siswa = $this->getSiswa();
        abort_if($tugas->jadwal->kelas_id !== $siswa->kelas_id, 403, 'Akses ditolak.');
        abort_if($tugas->is_expired, 422, 'Deadline tugas sudah lewat.');

        $sudah = PengumpulanTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $siswa->id)
            ->exists();
        abort_if($sudah, 422, 'Kamu sudah mengumpulkan tugas ini.');

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

        $pengumpulan = PengumpulanTugas::create($data);

        return (new PengumpulanTugasResource($pengumpulan))
            ->additional(['message' => 'Tugas berhasil dikumpulkan.'])
            ->response()
            ->setStatusCode(201);
    }
}
