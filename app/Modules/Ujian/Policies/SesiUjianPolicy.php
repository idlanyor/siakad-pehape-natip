<?php

namespace App\Modules\Ujian\Policies;

use App\Models\User;
use App\Modules\Ujian\Models\SesiUjian;
use Illuminate\Support\Facades\Gate;

class SesiUjianPolicy
{
    /**
     * Siswa pemilik sesi, atau siapa pun yang boleh menilai ujiannya.
     */
    public function view(User $user, SesiUjian $sesi): bool
    {
        return $this->milikSiswa($user, $sesi) || Gate::forUser($user)->allows('nilai', $sesi->ujian);
    }

    /** Menjawab, menyelesaikan, dan melaporkan pelanggaran: hanya siswa pemilik. */
    public function kerjakan(User $user, SesiUjian $sesi): bool
    {
        return $this->milikSiswa($user, $sesi);
    }

    /** Paksa selesai, diskualifikasi, reset. */
    public function awasi(User $user, SesiUjian $sesi): bool
    {
        return Gate::forUser($user)->allows('awasi', $sesi->ujian);
    }

    public function nilai(User $user, SesiUjian $sesi): bool
    {
        return Gate::forUser($user)->allows('nilai', $sesi->ujian);
    }

    private function milikSiswa(User $user, SesiUjian $sesi): bool
    {
        return $user->isSiswa() && (int) $user->siswa?->id === (int) $sesi->siswa_id;
    }
}
