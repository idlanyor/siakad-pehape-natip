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
        Schema::create('jurnal_pkls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penempatan_pkl_id')->constrained('penempatan_pkls')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('kegiatan');
            $table->string('foto')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['penempatan_pkl_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_pkls');
    }
};
