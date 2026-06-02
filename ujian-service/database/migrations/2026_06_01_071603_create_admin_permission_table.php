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
        Schema::create('admin_permission', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., bank_soal.kelola, proktor.monitor
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('grup')->nullable(); // untuk grouping permission di UI
            $table->timestamps();
            
            // Index
            $table->index('grup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_permission');
    }
};