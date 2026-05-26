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
        Schema::create('nilai_pkls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penempatan_pkl_id')->constrained('penempatan_pkls')->cascadeOnDelete();
            $table->decimal('nilai_pembimbing', 5, 2)->nullable();
            $table->decimal('nilai_du_di', 5, 2)->nullable();
            $table->decimal('nilai_sikap', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('penempatan_pkl_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_pkls');
    }
};
