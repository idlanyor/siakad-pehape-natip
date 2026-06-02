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
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siakad_id')->unique()->comment('ID siswa di SIAKAD utama');
            $table->string('nis', 20)->unique();
            $table->string('nisn', 20)->nullable()->unique();
            $table->string('nama_lengkap');
            $table->unsignedBigInteger('kelas_id')->comment('ID kelas di SIAKAD');
            $table->string('kelas_nama')->nullable();
            $table->integer('tingkat')->nullable()->comment('10, 11, 12');
            $table->string('username')->unique();
            $table->string('email')->nullable();
            $table->enum('status', ['aktif', 'lulus', 'keluar', 'mutasi'])->default('aktif');
            $table->timestamp('synced_at')->nullable()->comment('Last sync from SIAKAD');
            $table->timestamps();

            $table->index('siakad_id');
            $table->index('kelas_id');
            $table->index('status');
            $table->index('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
