<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesi_ujian', function (Blueprint $table) {
            $table->timestamp('last_activity')->nullable()->after('tab_switch_count');
        });

        Schema::create('proktor_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_ujian_id')->constrained('sesi_ujian')->onDelete('cascade');
            $table->string('event_type');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['sesi_ujian_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proktor_events');

        Schema::table('sesi_ujian', function (Blueprint $table) {
            $table->dropColumn('last_activity');
        });
    }
};
