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
        Schema::create('guru_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('menu_jadwal')->default(true);
            $table->boolean('menu_mapel')->default(true);
            $table->boolean('menu_absensi')->default(true);
            $table->boolean('menu_nilai')->default(true);
            $table->boolean('menu_pkl')->default(true);
            $table->boolean('menu_ekskul')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guru_permissions');
    }
};
