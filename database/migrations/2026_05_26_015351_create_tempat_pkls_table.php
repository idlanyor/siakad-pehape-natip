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
        Schema::create('tempat_pkls', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('bidang_usaha')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kontak_person')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->unsignedSmallInteger('kuota')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tempat_pkls');
    }
};
