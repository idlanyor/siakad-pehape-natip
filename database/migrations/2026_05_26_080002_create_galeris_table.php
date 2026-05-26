<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeris', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('cover')->nullable();
            $table->date('tanggal');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('galeri_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('galeri_id')->constrained()->cascadeOnDelete();
            $table->string('foto');
            $table->string('keterangan')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri_items');
        Schema::dropIfExists('galeris');
    }
};
