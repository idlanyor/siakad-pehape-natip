<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesi_ujian', function (Blueprint $table) {
            $table->string('status', 50)->default('belum')->change();
            $table->decimal('nilai', 5, 2)->nullable()->after('nilai_akhir');
            $table->unsignedInteger('violation_count')->default(0)->after('tab_switch_count');
        });

        Schema::table('jawaban_siswa', function (Blueprint $table) {
            $table->unsignedBigInteger('soal_id')->nullable()->after('soal_ujian_id');
            $table->text('jawaban')->nullable()->after('jawaban_text');
        });
    }

    public function down(): void
    {
        Schema::table('jawaban_siswa', function (Blueprint $table) {
            $table->dropColumn(['soal_id', 'jawaban']);
        });

        Schema::table('sesi_ujian', function (Blueprint $table) {
            $table->dropColumn(['nilai', 'violation_count']);
        });
    }
};
