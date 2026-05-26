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
        Schema::create('anggota_organisasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jabatan_organisasi_id')->constrained()->cascadeOnDelete();
            $table->string('nama', 150);
            $table->foreignId('guru_id')->nullable()->constrained('gurus')->nullOnDelete();
            $table->string('foto', 255)->nullable();
            $table->string('nip', 50)->nullable();
            $table->string('periode', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota_organisasis');
    }
};
