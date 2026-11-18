<?php

namespace App\Modules\Ujian\Policies;

use App\Models\User;

/**
 * Periode dan fase ujian dikelola admin; guru hanya melihat (untuk memilih
 * periode saat membuat ujian).
 */
class PeriodeUjianPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isGuru();
    }

    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user): bool
    {
        return $user->isAdmin();
    }

    /** Termasuk paksa buka/tutup fase. */
    public function kelolaFase(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }
}
