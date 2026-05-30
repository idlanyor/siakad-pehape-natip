<?php

namespace App\Policies;

use App\Models\PembayaranSpp;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PembayaranSppPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, PembayaranSpp $pembayaranSpp): bool
    {
        return $user->isAdmin() || ($user->isSiswa() && $user->siswa?->id === $pembayaranSpp->tagihan->siswa_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSiswa();
    }

    public function update(User $user, PembayaranSpp $pembayaranSpp): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, PembayaranSpp $pembayaranSpp): bool
    {
        return $user->isAdmin();
    }
}
