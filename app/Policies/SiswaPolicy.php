<?php

namespace App\Policies;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SiswaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isGuru();
    }

    public function view(User $user, Siswa $siswa): bool
    {
        return $user->isAdmin() || $user->isGuru() || ($user->isSiswa() && $user->siswa?->id === $siswa->id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Siswa $siswa): bool
    {
        return $user->isAdmin() || ($user->isSiswa() && $user->siswa?->id === $siswa->id);
    }

    public function delete(User $user, Siswa $siswa): bool
    {
        return $user->isAdmin();
    }
}
