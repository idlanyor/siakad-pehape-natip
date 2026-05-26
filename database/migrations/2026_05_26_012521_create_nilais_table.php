<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwals')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->decimal('tugas', 5, 2)->nullable();
            $table->decimal('pts', 5, 2)->nullable();
            $table->decimal('pas', 5, 2)->nullable();
            $table->decimal('praktik', 5, 2)->nullable();
            $table->decimal('proyek', 5, 2)->nullable();
            $table->decimal('portofolio', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['jadwal_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};
