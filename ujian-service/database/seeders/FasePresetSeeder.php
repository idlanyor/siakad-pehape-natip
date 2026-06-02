<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PeriodeUjian;
use App\Models\FasePeriode;
use Carbon\Carbon;

class FasePresetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeder ini membuat contoh periode ujian dengan fase-fase preset
     * untuk keperluan development dan testing.
     */
    public function run(): void
    {
        // Contoh Periode 1: Ujian Tengah Semester (UTS)
        $periodeUTS = PeriodeUjian::create([
            'nama' => 'Ujian Tengah Semester Ganjil 2025/2026',
            'tahun_ajaran' => '2025/2026',
            'semester' => 'ganjil',
            'status' => 'draft',
        ]);

        // Fase untuk UTS (durasi lebih pendek)
        $startDate = Carbon::now()->addDays(7);
        
        FasePeriode::create([
            'periode_id' => $periodeUTS->id,
            'tipe' => 'input_soal',
            'urutan' => 1,
            'tgl_mulai' => $startDate->copy(),
            'tgl_selesai' => $startDate->copy()->addDays(7),
        ]);

        FasePeriode::create([
            'periode_id' => $periodeUTS->id,
            'tipe' => 'try_out',
            'urutan' => 2,
            'tgl_mulai' => $startDate->copy()->addDays(8),
            'tgl_selesai' => $startDate->copy()->addDays(10),
        ]);

        FasePeriode::create([
            'periode_id' => $periodeUTS->id,
            'tipe' => 'pelaksanaan',
            'urutan' => 3,
            'tgl_mulai' => $startDate->copy()->addDays(11),
            'tgl_selesai' => $startDate->copy()->addDays(14),
        ]);

        FasePeriode::create([
            'periode_id' => $periodeUTS->id,
            'tipe' => 'remedial',
            'urutan' => 4,
            'tgl_mulai' => $startDate->copy()->addDays(15),
            'tgl_selesai' => $startDate->copy()->addDays(17),
        ]);

        FasePeriode::create([
            'periode_id' => $periodeUTS->id,
            'tipe' => 'rekapitulasi',
            'urutan' => 5,
            'tgl_mulai' => $startDate->copy()->addDays(18),
            'tgl_selesai' => $startDate->copy()->addDays(21),
        ]);

        // Contoh Periode 2: Ujian Akhir Semester (UAS)
        $periodeUAS = PeriodeUjian::create([
            'nama' => 'Ujian Akhir Semester Ganjil 2025/2026',
            'tahun_ajaran' => '2025/2026',
            'semester' => 'ganjil',
            'status' => 'draft',
        ]);

        // Fase untuk UAS (durasi lebih panjang)
        $startDateUAS = Carbon::now()->addDays(60);
        
        FasePeriode::create([
            'periode_id' => $periodeUAS->id,
            'tipe' => 'input_soal',
            'urutan' => 1,
            'tgl_mulai' => $startDateUAS->copy(),
            'tgl_selesai' => $startDateUAS->copy()->addDays(14),
        ]);

        FasePeriode::create([
            'periode_id' => $periodeUAS->id,
            'tipe' => 'try_out',
            'urutan' => 2,
            'tgl_mulai' => $startDateUAS->copy()->addDays(15),
            'tgl_selesai' => $startDateUAS->copy()->addDays(18),
        ]);

        FasePeriode::create([
            'periode_id' => $periodeUAS->id,
            'tipe' => 'pelaksanaan',
            'urutan' => 3,
            'tgl_mulai' => $startDateUAS->copy()->addDays(19),
            'tgl_selesai' => $startDateUAS->copy()->addDays(25),
        ]);

        FasePeriode::create([
            'periode_id' => $periodeUAS->id,
            'tipe' => 'remedial',
            'urutan' => 4,
            'tgl_mulai' => $startDateUAS->copy()->addDays(26),
            'tgl_selesai' => $startDateUAS->copy()->addDays(30),
        ]);

        FasePeriode::create([
            'periode_id' => $periodeUAS->id,
            'tipe' => 'rekapitulasi',
            'urutan' => 5,
            'tgl_mulai' => $startDateUAS->copy()->addDays(31),
            'tgl_selesai' => $startDateUAS->copy()->addDays(35),
        ]);

        // Contoh Periode 3: Periode Aktif (untuk testing)
        $periodeAktif = PeriodeUjian::create([
            'nama' => 'Periode Testing - Aktif',
            'tahun_ajaran' => '2025/2026',
            'semester' => 'ganjil',
            'status' => 'aktif',
        ]);

        // Fase aktif saat ini (input_soal)
        $now = Carbon::now();
        
        FasePeriode::create([
            'periode_id' => $periodeAktif->id,
            'tipe' => 'input_soal',
            'urutan' => 1,
            'tgl_mulai' => $now->copy()->subDays(2),
            'tgl_selesai' => $now->copy()->addDays(5),
        ]);

        FasePeriode::create([
            'periode_id' => $periodeAktif->id,
            'tipe' => 'try_out',
            'urutan' => 2,
            'tgl_mulai' => $now->copy()->addDays(6),
            'tgl_selesai' => $now->copy()->addDays(8),
        ]);

        FasePeriode::create([
            'periode_id' => $periodeAktif->id,
            'tipe' => 'pelaksanaan',
            'urutan' => 3,
            'tgl_mulai' => $now->copy()->addDays(9),
            'tgl_selesai' => $now->copy()->addDays(12),
        ]);

        FasePeriode::create([
            'periode_id' => $periodeAktif->id,
            'tipe' => 'remedial',
            'urutan' => 4,
            'tgl_mulai' => $now->copy()->addDays(13),
            'tgl_selesai' => $now->copy()->addDays(15),
        ]);

        FasePeriode::create([
            'periode_id' => $periodeAktif->id,
            'tipe' => 'rekapitulasi',
            'urutan' => 5,
            'tgl_mulai' => $now->copy()->addDays(16),
            'tgl_selesai' => $now->copy()->addDays(20),
        ]);

        // Refresh fase aktif untuk periode aktif
        $periodeAktif->refreshFaseAktif();

        $this->command->info('✓ Seeder fase preset berhasil dijalankan!');
        $this->command->info("  - Periode UTS: {$periodeUTS->nama}");
        $this->command->info("  - Periode UAS: {$periodeUAS->nama}");
        $this->command->info("  - Periode Aktif: {$periodeAktif->nama} (Fase: {$periodeAktif->fase_aktif})");
    }
}