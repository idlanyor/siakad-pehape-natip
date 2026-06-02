<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sesi_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->onDelete('cascade');
            
            // Ref snapshot siswa (dari SIAKAD, bukan FK)
            $table->unsignedBigInteger('siswa_id');
            $table->string('siswa_nama');
            $table->string('siswa_nis', 50)->nullable();
            $table->unsignedBigInteger('kelas_id')->nullable();
            
            // Status dan fase sesi
            $table->enum('status', ['belum', 'berlangsung', 'selesai', 'timeout', 'diskualifikasi'])->default('belum');
            $table->boolean('is_resmi')->default(true); // false untuk try_out
            $table->enum('fase', ['try_out', 'pelaksanaan', 'remedial'])->default('pelaksanaan');
            
            // Waktu sesi
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->unsignedInteger('durasi_detik')->default(0); // durasi aktual dalam detik
            
            // Nilai
            $table->decimal('nilai_otomatis', 5, 2)->nullable(); // nilai dari koreksi otomatis PG
            $table->decimal('nilai_manual', 5, 2)->nullable(); // nilai dari koreksi manual essay
            $table->decimal('nilai_akhir', 5, 2)->nullable(); // nilai final
            $table->string('predikat', 2)->nullable(); // A, B, C, D, E
            
            // Anti-curang tracking
            $table->unsignedInteger('tab_switch_count')->default(0);
            
            $table->timestamps();
            
            // Index untuk query performance
            $table->unique(['ujian_id', 'siswa_id']); // satu sesi per siswa per ujian
            $table->index(['ujian_id', 'status']);
            $table->index(['siswa_id', 'is_resmi']);
            $table->index('fase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_ujian');
    }
};