<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gelombang_ppdbs', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('tahun_ajaran', 10)->default('2026/2027');
            $table->dateTime('tanggal_buka');
            $table->dateTime('tanggal_tutup');
            $table->smallInteger('kuota')->default(0)->comment('0 = tidak terbatas');
            $table->text('keterangan')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('persyaratan_ppdbs', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->smallInteger('urutan')->default(0);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('faq_ppdbs', function (Blueprint $table) {
            $table->id();
            $table->string('pertanyaan');
            $table->text('jawaban');
            $table->smallInteger('urutan')->default(0);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_ppdbs');
        Schema::dropIfExists('persyaratan_ppdbs');
        Schema::dropIfExists('gelombang_ppdbs');
    }
};
