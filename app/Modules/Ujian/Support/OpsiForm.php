<?php

namespace App\Modules\Ujian\Support;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use App\Modules\Ujian\Models\PeriodeUjian;

/**
 * Pilihan dropdown untuk form modul ujian, dalam format [{value, label}].
 */
class OpsiForm
{
    /** Admin: semua mapel. Guru: hanya mapel yang diampu. */
    public static function mapel(User $user): array
    {
        $query = $user->isAdmin()
            ? Mapel::query()
            : ($user->guru?->mapels() ?? Mapel::query()->whereRaw('1 = 0'));

        return $query->orderBy('nama_mapel')->get(['mapels.id', 'nama_mapel'])
            ->map(fn ($m) => ['value' => $m->id, 'label' => $m->nama_mapel])
            ->all();
    }

    public static function kelas(): array
    {
        return Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas'])
            ->map(fn ($k) => ['value' => $k->id, 'label' => $k->nama_kelas])
            ->all();
    }

    public static function guru(): array
    {
        return Guru::orderBy('nama')->get(['id', 'nama'])
            ->map(fn ($g) => ['value' => $g->id, 'label' => $g->nama])
            ->all();
    }

    public static function periodeTerbuka(): array
    {
        return PeriodeUjian::where('status', '!=', 'selesai')->latest()->get(['id', 'nama'])
            ->map(fn ($p) => ['value' => $p->id, 'label' => $p->nama])
            ->all();
    }
}
