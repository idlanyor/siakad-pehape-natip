<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SesiUjian;
use App\Models\Ujian;
use App\Support\SesiPresenter;
use Illuminate\Http\Request;

class UjianController extends Controller
{
    /** Daftar ujian aktif untuk kelas siswa */
    public function index(Request $request)
    {
        $ctx = $request->input('_auth_user');
        $now = now();

        $ujians = Ujian::where('is_published', true)
            ->where('kelas_id', $ctx['kelas_id'])
            ->where('waktu_mulai', '<=', $now)
            ->where('waktu_selesai', '>=', $now)
            ->orderBy('waktu_mulai')
            ->get();

        $sesiByUjian = SesiUjian::where('siswa_id', $ctx['user_id'])
            ->where('is_resmi', true)
            ->get()->keyBy('ujian_id');

        return view('siswa.ujian.index', compact('ujians', 'sesiByUjian'));
    }

    /** Halaman pengerjaan */
    public function show(Request $request, SesiUjian $sesi)
    {
        $this->authorizeSesi($request, $sesi);

        $payload = (new SesiPresenter())->forPengerjaan($sesi);
        $sesi->setAttribute('sisa_waktu_detik', $payload['sisa_waktu_detik']);
        $soals = $payload['soals'];
        $progress = $payload['progress'];

        return view('siswa.sesi.show', compact('sesi', 'soals', 'progress'));
    }

    /** Halaman hasil */
    public function result(Request $request, SesiUjian $sesi)
    {
        $this->authorizeSesi($request, $sesi);
        abort_unless(
            in_array($sesi->status, ['selesai', 'timeout', 'diskualifikasi'], true),
            422, 'Sesi ujian belum selesai'
        );

        $sesi->load(['ujian', 'jawabans.soal']);

        $jawabans = $sesi->jawabans->map(function ($j) {
            $soal = $j->soal;
            return (object) [
                'nomor_urut' => $j->nomor_urut,
                'tipe' => $soal->tipe,
                'pertanyaan' => $soal->pertanyaan,
                'opsi' => [
                    'A' => $soal->opsi_a, 'B' => $soal->opsi_b, 'C' => $soal->opsi_c,
                    'D' => $soal->opsi_d, 'E' => $soal->opsi_e,
                ],
                'kunci_jawaban' => $soal->kunci_jawaban,
                'jawaban' => $j->jawaban,
                'is_benar' => $j->is_benar,
                'nilai' => $j->nilai,
                'feedback' => $j->feedback ?? null,
                'pembahasan' => $soal->pembahasan ?? null,
            ];
        });

        $statistik = [
            'benar' => $sesi->jawabans->where('is_benar', true)->count(),
            'salah' => $sesi->jawabans->where('is_benar', false)->count(),
            'belum_dinilai' => $sesi->jawabans->whereNull('is_benar')->count(),
        ];

        return view('siswa.sesi.result', compact('sesi', 'jawabans', 'statistik'));
    }

    private function authorizeSesi(Request $request, SesiUjian $sesi): void
    {
        $ctx = $request->input('_auth_user');
        abort_if(!$ctx || $sesi->siswa_id != $ctx['user_id'], 403, 'Unauthorized');
    }
}
