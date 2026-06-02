<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use App\Models\UserSiswa;
use App\Services\SiakadSyncService;
use Illuminate\Console\Command;

class SyncSiswaFromSiakad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siakad:sync-siswa 
                            {--kelas=* : Filter by kelas IDs}
                            {--force : Force sync all data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync siswa data from SIAKAD and generate user_siswa accounts';

    protected $siakadSync;

    /**
     * Create a new command instance.
     */
    public function __construct(SiakadSyncService $siakadSync)
    {
        parent::__construct();
        $this->siakadSync = $siakadSync;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting siswa sync from SIAKAD...');
        $this->newLine();

        // Get kelas filter if provided
        $kelasIds = $this->option('kelas');

        // Sync siswa data from SIAKAD
        $this->info('Fetching siswa data from SIAKAD...');
        $result = $this->siakadSync->syncAllSiswa($kelasIds);

        if (!$result['success']) {
            $this->error('Failed to sync siswa data from SIAKAD');
            $this->error($result['message']);
            if (isset($result['error'])) {
                $this->error($result['error']);
            }
            return 1;
        }

        $this->info("✓ Synced {$result['total']} siswa records");
        $this->info("  - Created: {$result['created']}");
        $this->info("  - Updated: {$result['updated']}");
        $this->newLine();

        // Generate user_siswa accounts
        $this->info('Generating user_siswa accounts...');
        $generated = 0;
        $updated = 0;
        $errors = 0;

        $siswaList = Siswa::all();
        $bar = $this->output->createProgressBar($siswaList->count());
        $bar->start();

        foreach ($siswaList as $siswa) {
            try {
                $userSiswa = UserSiswa::where('siswa_id', $siswa->id)->first();
                
                if ($userSiswa) {
                    // Update existing
                    $userSiswa->username = $siswa->nis;
                    $userSiswa->save();
                    $updated++;
                } else {
                    // Create new
                    UserSiswa::createOrUpdateFromSiswa($siswa);
                    $generated++;
                }
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("Error for siswa {$siswa->nis}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ User siswa accounts processed:");
        $this->info("  - Generated: {$generated}");
        $this->info("  - Updated: {$updated}");
        if ($errors > 0) {
            $this->warn("  - Errors: {$errors}");
        }
        $this->newLine();

        $this->info('Sync completed successfully!');
        $this->info('Default password for all siswa: NIS masing-masing');
        
        return 0;
    }
}