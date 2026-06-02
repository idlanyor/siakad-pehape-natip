<?php

namespace App\Console\Commands;

use App\Models\SesiUjian;
use Illuminate\Console\Command;

class AutoFinishUjianCommand extends Command
{
    protected $signature = 'ujian:auto-finish';

    protected $description = 'Finish active exam sessions that have exceeded their duration.';

    public function handle(): int
    {
        $finished = 0;

        SesiUjian::whereIn('status', ['berlangsung', 'sedang_mengerjakan'])
            ->whereNotNull('waktu_mulai')
            ->get()
            ->each(function (SesiUjian $sesi) use (&$finished) {
                $duration = $sesi->durasi_detik > 0 ? (int) $sesi->durasi_detik : max((int) $sesi->ujian->durasi_menit, 1) * 60;

                if ($sesi->waktu_mulai->copy()->addSeconds($duration)->isFuture()) {
                    return;
                }

                $sesi->update([
                    'status' => 'selesai',
                    'waktu_selesai' => now(),
                ]);
                $finished++;
            });

        $this->info("Auto-finished {$finished} sesi ujian.");

        return self::SUCCESS;
    }
}
