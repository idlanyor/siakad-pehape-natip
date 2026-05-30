<?php

namespace App\Policies;

use App\Models\PendaftarPpdb;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PendaftarPpdbPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, PendaftarPpdb $pendaftarPpdb): bool
    {
        return $user->isAdmin() || ($user->isPendaftar() && $user->pendaftarPpdb?->id === $pendaftarPpdb->id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isPendaftar();
    }

    public function update(User $user, PendaftarPpdb $pendaftarPpdb): bool
    {
        return $user->isAdmin() || ($user->isPendaftar() && $user->pendaftarPpdb?->id === $pendaftarPpdb->id);
    }

    public function delete(User $user, PendaftarPpdb $pendaftarPpdb): bool
    {
        return $user->isAdmin();
    }
}
