<?php

namespace App\Observers;

use App\Models\SesiUjian;
use App\Services\ELearningNotifier;

class SesiUjianObserver
{
    /**
     * Handle the SesiUjian "updated" event.
     * Trigger notifikasi saat nilai_akhir diisi (koreksi selesai).
     */
    public function updated(SesiUjian $sesiUjian): void
    {
        // Cek apakah nilai_akhir baru saja diisi (dari null → ada nilai)
        if ($sesiUjian->isDirty('nilai_akhir') && $sesiUjian->nilai_akhir !== null) {
            ELearningNotifier::ujianDikoreksi($sesiUjian);
        }
    }
}
