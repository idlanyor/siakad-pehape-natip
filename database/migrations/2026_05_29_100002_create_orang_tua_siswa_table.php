<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orang_tua_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orang_tua_id')->constrained('orang_tuas')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->enum('hubungan', ['ayah', 'ibu', 'wali'])->default('wali');
            $table->timestamps();

            $table->unique(['orang_tua_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orang_tua_siswa');
    }
};
