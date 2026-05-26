<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('halaman_statis', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('judul');
            $table->longText('konten')->nullable();
            $table->string('meta_desc', 255)->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('halaman_statis');
    }
};
