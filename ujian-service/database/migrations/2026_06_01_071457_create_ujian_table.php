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
        Schema::create('ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode_ujian')->onDelete('cascade');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['quiz', 'uts', 'uas', 'remidi'])->default('quiz');
            
            // Ref snapshot dari SIAKAD (bukan FK, hanya snapshot)
            $table->unsignedBigInteger('jadwal_id')->nullable();
            $table->unsignedBigInteger('mapel_id')->nullable();
            $table->string('mapel_nama')->nullable();
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->string('kelas_nama')->nullable();
            $table->unsignedBigInteger('guru_id')->nullable();
            $table->string('guru_nama')->nullable();
            
            // Konfigurasi ujian
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai');
            $table->unsignedInteger('durasi_menit'); // durasi dalam menit
            $table->decimal('passing_grade', 5, 2)->default(0); // nilai minimal lulus
            $table->boolean('acak_soal')->default(false);
            $table->boolean('acak_opsi')->default(false);
            $table->boolean('tampilkan_nilai')->default(true);
            $table->enum('status', ['draft', 'published', 'selesai'])->default('draft');
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('jumlah_peserta')->default(0);
            
            $table->timestamps();
            
            // Index untuk query performance
            $table->index(['periode_id', 'status']);
            $table->index(['kelas_id', 'status']);
            $table->index(['waktu_mulai', 'waktu_selesai']);
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujian');
    }
};