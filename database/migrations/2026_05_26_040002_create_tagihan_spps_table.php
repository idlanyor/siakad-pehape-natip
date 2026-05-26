<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_spps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->date('bulan'); // Simpan sebagai tanggal 1, e.g. 2026-01-01
            $table->decimal('nominal', 10, 2);
            $table->enum('status', ['belum', 'cicil', 'lunas'])->default('belum');
            $table->string('keterangan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['siswa_id', 'bulan']); // 1 tagihan per siswa per bulan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_spps');
    }
};
