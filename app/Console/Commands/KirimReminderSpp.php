<?php

namespace App\Console\Commands;

use App\Jobs\SendWaNotification;
use App\Models\TagihanSpp;
use App\Services\WaTemplates;
use Illuminate\Console\Command;

class KirimReminderSpp extends Command
{
    protected $signature = 'reminder:spp';
    protected $description = 'Kirim reminder WA untuk tagihan SPP yang belum lunas (H-3 dan H-1)';

    public function handle(): int
    {
        if (!config('services.whatsapp.enabled')) {
            $this->info('WhatsApp notification disabled.');
            return self::SUCCESS;
        }

        $today = today();

        // Tagihan yang belum lunas dengan bulan = bulan ini
        $tagihans = TagihanSpp::with('siswa')
            ->whereIn('status', ['belum', 'cicil'])
            ->whereYear('bulan', $today->year)
            ->whereMonth('bulan', $today->month)
            ->get();

        $sent = 0;

        foreach ($tagihans as $tagihan) {
            $siswa = $tagihan->siswa;
            if (!$siswa) continue;

            $phone = $siswa->no_wa ?? $siswa->no_hp ?? null;
            if (empty($phone)) continue;

            $message = WaTemplates::sppJatuhTempo($siswa, $tagihan);
            SendWaNotification::dispatch($phone, $message, 'reminder_spp');
            $sent++;
        }

        $this->info("Reminder SPP terkirim: {$sent} pesan.");
        return self::SUCCESS;
    }
}
