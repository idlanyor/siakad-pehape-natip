<?php

namespace App\Modules\Ujian\Http\Controllers;

use App\Modules\Ujian\Models\SesiUjian;
use App\Modules\Ujian\Models\Ujian;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $ujian = Ujian::query()
            ->when($user->isGuru(), fn ($q) => $q->where(fn ($w) => $w
                ->where('guru_id', $user->guru?->id ?? 0)
                ->orWhereHas('pengawas', fn ($p) => $p->whereKey($user->guru?->id ?? 0))))
            ->when($user->isSiswa(), fn ($q) => $q->published()->where('kelas_id', $user->siswa?->kelas_id ?? 0))
            ->when(! ($user->isAdmin() || $user->isGuru() || $user->isSiswa()), fn ($q) => $q->whereRaw('1 = 0'));

        $mendatang = (clone $ujian)
            ->with(['mapel:id,nama_mapel', 'kelas:id,nama_kelas'])
            ->where('waktu_selesai', '>=', now())
            ->orderBy('waktu_mulai')
            ->limit(8)
            ->get()
            ->map(fn (Ujian $u) => [
                'id' => $u->id,
                'judul' => $u->judul,
                'mapel' => $u->mapel?->nama_mapel,
                'kelas' => $u->kelas?->nama_kelas,
                'status_label' => $u->status_label,
                'status_badge' => $u->status_badge,
                'waktu_mulai' => $u->waktu_mulai,
                'waktu_selesai' => $u->waktu_selesai,
                'sedang_berlangsung' => $u->isDibuka(),
            ]);

        return Inertia::render('Dashboard', [
            'statistik' => $user->isSiswa() ? null : [
                'draft' => (clone $ujian)->where('status', Ujian::STATUS_DRAFT)->count(),
                'terbit' => (clone $ujian)->published()->count(),
                'berlangsung' => (clone $ujian)->published()
                    ->where('waktu_mulai', '<=', now())->where('waktu_selesai', '>=', now())->count(),
                'peserta_aktif' => SesiUjian::whereIn('ujian_id', (clone $ujian)->select('id'))
                    ->where('status', SesiUjian::STATUS_BERLANGSUNG)->count(),
            ],
            'mendatang' => $mendatang,
        ]);
    }
}
