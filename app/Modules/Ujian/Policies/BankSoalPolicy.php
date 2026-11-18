<?php

namespace App\Modules\Ujian\Policies;

use App\Models\User;
use App\Modules\Ujian\Models\BankSoal;

/**
 * Bank soal dipakai bersama per mapel: guru pengampu mapel boleh melihat dan
 * memakai soal, tetapi hanya pembuatnya yang boleh mengubah/menghapus.
 */
class BankSoalPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isGuru();
    }

    public function view(User $user, BankSoal $soal): bool
    {
        return $this->pembuat($user, $soal) || $this->pengampu($user, $soal);
    }

    public function create(User $user): bool
    {
        return $user->isGuru();
    }

    public function update(User $user, BankSoal $soal): bool
    {
        return $this->pembuat($user, $soal);
    }

    public function delete(User $user, BankSoal $soal): bool
    {
        return $this->pembuat($user, $soal);
    }

    private function pembuat(User $user, BankSoal $soal): bool
    {
        return $user->isGuru() && (int) $soal->created_by === (int) $user->id;
    }

    private function pengampu(User $user, BankSoal $soal): bool
    {
        return $user->isGuru()
            && $soal->mapel_id !== null
            && (bool) $user->guru?->mapels()->whereKey($soal->mapel_id)->exists();
    }
}
