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
        Schema::table('jadwals', function (Blueprint $table) {
            $table->foreignId('kelas_id')->nullable()->after('mapel_id')->constrained('kelas')->nullOnDelete();
            $table->foreignId('guru_id')->nullable()->after('kelas_id')->constrained('gurus')->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->after('guru_id')->constrained('semesters')->nullOnDelete();
            $table->string('jam_mulai', 5)->after('hari')->nullable();
            $table->string('jam_selesai', 5)->after('jam_mulai')->nullable();
            $table->string('ruangan')->nullable()->after('jam_selesai');
            $table->string('waktu')->nullable()->change(); // buat nullable agar backward compat
        });
    }

    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropForeign(['guru_id']);
            $table->dropForeign(['semester_id']);
            $table->dropColumn(['kelas_id', 'guru_id', 'semester_id', 'jam_mulai', 'jam_selesai', 'ruangan']);
        });
    }
};
