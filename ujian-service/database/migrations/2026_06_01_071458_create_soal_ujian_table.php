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
        Schema::create('soal_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->onDelete('cascade');
            $table->foreignId('bank_soal_id')->nullable()->constrained('bank_soal')->onDelete('set null');
            $table->unsignedInteger('nomor'); // nomor urut soal
            $table->enum('tipe', ['pilihan_ganda', 'essay'])->default('pilihan_ganda');
            
            // Konten soal (rich text/HTML)
            $table->text('pertanyaan');
            
            // Opsi untuk pilihan ganda
            $table->text('opsi_a')->nullable();
            $table->text('opsi_b')->nullable();
            $table->text('opsi_c')->nullable();
            $table->text('opsi_d')->nullable();
            $table->text('opsi_e')->nullable();
            
            // Kunci jawaban
            $table->string('kunci_jawaban', 1)->nullable(); // A, B, C, D, E untuk PG
            
            // Bobot nilai
            $table->decimal('bobot_nilai', 5, 2)->default(1.00);
            
            $table->timestamps();
            
            // Index untuk query performance
            $table->index(['ujian_id', 'nomor']);
            $table->index('bank_soal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soal_ujian');
    }
};