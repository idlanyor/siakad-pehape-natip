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
        Schema::create('media_soal', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship
            $table->string('owner_type'); // soal_ujian, bank_soal, opsi
            $table->unsignedBigInteger('owner_id');
            
            // Metadata media
            $table->enum('jenis', ['gambar', 'audio', 'video'])->default('gambar');
            $table->string('path'); // path ke file di storage
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('ukuran')->nullable(); // ukuran file dalam bytes
            
            $table->timestamps();
            
            // Index untuk query performance
            $table->index(['owner_type', 'owner_id']);
            $table->index('jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_soal');
    }
};