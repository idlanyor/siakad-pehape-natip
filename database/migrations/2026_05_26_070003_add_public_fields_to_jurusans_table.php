<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurusans', function (Blueprint $table) {
            $table->text('deskripsi_publik')->nullable()->after('nama_jurusan');
            $table->string('gambar')->nullable()->after('deskripsi_publik');
            $table->string('icon', 50)->nullable()->after('gambar')
                ->comment('Font Awesome class, e.g. fas fa-laptop-code');
            $table->boolean('is_aktif_ppdb')->default(true)->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('jurusans', function (Blueprint $table) {
            $table->dropColumn(['deskripsi_publik', 'gambar', 'icon', 'is_aktif_ppdb']);
        });
    }
};
