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
        Schema::create('proktor_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_ujian_id')->constrained('sesi_ujian')->onDelete('cascade');
            
            // Tipe event anti-curang
            $table->enum('tipe', [
                'tab_switch',
                'blur',
                'copy',
                'paste',
                'fullscreen_exit',
                'reconnect',
                'right_click',
                'devtools_open'
            ]);
            
            // Detail event dalam JSON
            $table->json('detail')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            // Index untuk query performance dan realtime monitoring
            $table->index(['sesi_ujian_id', 'created_at']);
            $table->index(['sesi_ujian_id', 'tipe']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proktor_event');
    }
};