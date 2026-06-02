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
        Schema::create('fase_periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('periode_ujian')->onDelete('cascade');
            $table->enum('tipe', ['input_soal', 'try_out', 'pelaksanaan', 'remedial', 'rekapitulasi']);
            $table->unsignedTinyInteger('urutan'); // urutan fase dalam periode
            $table->dateTime('tgl_mulai');
            $table->dateTime('tgl_selesai');
            $table->enum('override_status', ['paksa_buka', 'paksa_tutup'])->nullable();
            $table->string('override_oleh')->nullable(); // user yang melakukan override
            $table->timestamp('override_at')->nullable();
            $table->timestamps();
            
            // Index untuk query performance
            $table->index(['periode_id', 'urutan']);
            $table->index(['periode_id', 'tipe']);
            $table->index(['tgl_mulai', 'tgl_selesai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fase_periode');
    }
};