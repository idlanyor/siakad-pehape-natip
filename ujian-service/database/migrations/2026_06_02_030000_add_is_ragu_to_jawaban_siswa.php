<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jawaban_siswa', function (Blueprint $table) {
            $table->boolean('is_ragu')->default(false)->after('is_benar');
        });
    }

    public function down(): void
    {
        Schema::table('jawaban_siswa', function (Blueprint $table) {
            $table->dropColumn('is_ragu');
        });
    }
};
