<?php

namespace App\Console\Commands;

use App\Models\PeriodeUjian;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshFaseAktifCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fase:refresh
                            {--periode_id= : ID periode tertentu untuk di-refresh}
                            {--force : Force refresh semua periode termasuk yang tidak aktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh fase aktif untuk periode ujian berdasarkan tanggal dan override';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai refresh fase aktif...');

        $query = PeriodeUjian::query();

        // Filter by periode_id if provided
        if ($this->option('periode_id')) {
            $query->where('id', $this->option('periode_id'));
        }

        // Only active periodes unless forced
        if (!$this->option('force')) {
            $query->where('status', 'aktif');
        }

        $periodes = $query->with('fases')->get();

        if ($periodes->isEmpty()) {
            $this->warn('Tidak ada periode yang perlu di-refresh.');
            return 0;
        }

        $this->info("Ditemukan {$periodes->count()} periode untuk di-refresh.");

        $refreshed = 0;
        $changed = 0;

        foreach ($periodes as $periode) {
            $oldFase = $periode->fase_aktif;
            $periode->refreshFaseAktif();
            $newFase = $periode->fase_aktif;

            $refreshed++;

            if ($oldFase !== $newFase) {
                $changed++;
                $this->line("  - Periode #{$periode->id} ({$periode->nama}): {$oldFase} → {$newFase}");
                
                // Log perubahan fase
                Log::info("Fase aktif berubah untuk periode {$periode->nama}", [
                    'periode_id' => $periode->id,
                    'old_fase' => $oldFase,
                    'new_fase' => $newFase,
                ]);
            }
        }

        $this->info("✓ Selesai! {$refreshed} periode di-refresh, {$changed} mengalami perubahan fase.");

        return 0;
    }
}