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
        Schema::create('bank_soal', function (Blueprint $table) {
            $table->id();
            
            // Ref snapshot mapel (dari SIAKAD)
            $table->unsignedBigInteger('mapel_id')->nullable();
            $table->string('mapel_nama')->nullable();
            
            // Metadata soal
            $table->string('topik')->nullable();
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit'])->default('sedang');
            $table->json('tag')->nullable(); // tags untuk kategorisasi
            
            // Konten soal
            $table->enum('tipe', ['pilihan_ganda', 'essay'])->default('pilihan_ganda');
            $table->text('pertanyaan');
            
            // Opsi untuk pilihan ganda
            $table->text('opsi_a')->nullable();
            $table->text('opsi_b')->nullable();
            $table->text('opsi_c')->nullable();
            $table->text('opsi_d')->nullable();
            $table->text('opsi_e')->nullable();
            
            // Kunci jawaban
            $table->string('kunci_jawaban', 1)->nullable(); // A, B, C, D, E
            
            // Bobot default
            $table->decimal('bobot_default', 5, 2)->default(1.00);
            
            // Audit
            $table->string('dibuat_oleh')->nullable(); // user yang membuat
            
            $table->timestamps();
            
            // Index untuk query performance
            $table->index('mapel_id');
            $table->index('tingkat_kesulitan');
            $table->index('tipe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_soal');
    }
};