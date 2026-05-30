<?php

namespace App\Policies;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GuruPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isGuru() || $user->isSiswa();
    }

    public function view(User $user, Guru $guru): bool
    {
        return $user->isAdmin() || $user->isSiswa() || ($user->isGuru() && $user->guru?->id === $guru->id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Guru $guru): bool
    {
        return $user->isAdmin() || ($user->isGuru() && $user->guru?->id === $guru->id);
    }

    public function delete(User $user, Guru $guru): bool
    {
        return $user->isAdmin();
    }
}
