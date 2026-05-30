<?php

namespace App\Policies;

use App\Models\Mapel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MapelPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Mapel $mapel): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Mapel $mapel): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Mapel $mapel): bool
    {
        return $user->isAdmin();
    }
}
