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
        Schema::create('periode_ujian', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // "UAS Ganjil 2026/2027"
            $table->string('tahun_ajaran', 20); // "2026/2027"
            $table->enum('semester', ['ganjil', 'genap']);
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
            $table->string('fase_aktif')->nullable(); // cache: hasil hitung tanggal + override
            $table->timestamps();
            
            // Index untuk query performance
            $table->index(['tahun_ajaran', 'semester']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_ujian');
    }
};