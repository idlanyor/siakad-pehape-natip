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
        Schema::create('jawaban_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_ujian_id')->constrained('sesi_ujian')->onDelete('cascade');
            $table->foreignId('soal_ujian_id')->constrained('soal_ujian')->onDelete('cascade');
            
            // Jawaban siswa
            $table->string('jawaban_pilihan', 1)->nullable(); // A, B, C, D, E untuk pilihan ganda
            $table->text('jawaban_text')->nullable(); // untuk essay
            
            // Penilaian
            $table->decimal('nilai', 5, 2)->nullable();
            $table->boolean('is_benar')->nullable(); // untuk pilihan ganda
            
            $table->timestamps();
            
            // Index untuk query performance
            $table->unique(['sesi_ujian_id', 'soal_ujian_id']); // satu jawaban per soal per sesi
            $table->index('sesi_ujian_id');
            $table->index('soal_ujian_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_siswa');
    }
};