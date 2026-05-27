<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumpulan_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained()->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained()->cascadeOnDelete();
            $table->text('konten'); // path file / url / isi teks
            $table->string('file_name')->nullable(); // nama file asli untuk download
            $table->text('catatan')->nullable();
            $table->float('nilai')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('dinilai_at')->nullable();
            $table->foreignId('dinilai_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tugas_id', 'siswa_id']); // 1 siswa hanya bisa kumpul 1x per tugas
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_tugas');
    }
};
