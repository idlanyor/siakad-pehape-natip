<?php

namespace App\Modules\Ujian\Policies;

use App\Models\User;
use App\Modules\Ujian\Models\Ujian;

/**
 * - Admin: semua ujian.
 * - Guru: ujian miliknya (ujian.guru_id), dan membuat ujian untuk mapel yang diampu.
 * - Guru pengawas (ujian_pengawas): melihat dan mengawasi, tidak mengubah/menilai.
 * - Siswa: melihat/mengerjakan ujian terbit untuk kelasnya.
 *
 * Versi ujian-service membandingkan ujian.guru_id dengan id user (tabel
 * berbeda), dan beberapa endpoint proktor hanya mengecek role.
 */
class UjianPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isGuru() || $user->isSiswa();
    }

    public function view(User $user, Ujian $ujian): bool
    {
        return $this->milikGuru($user, $ujian)
            || $this->pengawas($user, $ujian)
            || $this->untukSiswa($user, $ujian);
    }

    public function create(User $user): bool
    {
        return $user->isGuru() && $user->guru !== null;
    }

    /**
     * Guru hanya boleh membuat/memindah ujian ke mapel yang ia ampu.
     */
    public function gunakanMapel(User $user, int $mapelId): bool
    {
        return $user->isGuru() && (bool) $user->guru?->mapels()->whereKey($mapelId)->exists();
    }

    public function update(User $user, Ujian $ujian): bool
    {
        return $this->milikGuru($user, $ujian);
    }

    public function delete(User $user, Ujian $ujian): bool
    {
        return $this->milikGuru($user, $ujian) && ! $ujian->sesi()->exists();
    }

    /** Monitor peserta, paksa selesai, diskualifikasi, reset sesi. */
    public function awasi(User $user, Ujian $ujian): bool
    {
        return $this->milikGuru($user, $ujian) || $this->pengawas($user, $ujian);
    }

    /** Menugaskan pengawas: hanya admin (lewat before()). */
    public function tugaskanPengawas(User $user, Ujian $ujian): bool
    {
        return false;
    }

    /** Lihat hasil seluruh peserta dan koreksi essay. */
    public function nilai(User $user, Ujian $ujian): bool
    {
        return $this->milikGuru($user, $ujian);
    }

    public function kerjakan(User $user, Ujian $ujian): bool
    {
        return $this->untukSiswa($user, $ujian);
    }

    private function milikGuru(User $user, Ujian $ujian): bool
    {
        return $user->isGuru()
            && $ujian->guru_id !== null
            && (int) $ujian->guru_id === (int) $user->guru?->id;
    }

    private function pengawas(User $user, Ujian $ujian): bool
    {
        return $user->isGuru()
            && $user->guru !== null
            && $ujian->pengawas()->whereKey($user->guru->id)->exists();
    }

    private function untukSiswa(User $user, Ujian $ujian): bool
    {
        return $user->isSiswa()
            && $ujian->isPublished()
            && (int) $user->siswa?->kelas_id === (int) $ujian->kelas_id;
    }
}
