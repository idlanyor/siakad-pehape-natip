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
        Schema::create('user_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->string('username')->unique()->comment('Username untuk login (NIS)');
            $table->string('password')->comment('Password hash');
            $table->boolean('is_active')->default(true)->comment('Status aktif akun');
            $table->timestamp('last_login_at')->nullable()->comment('Waktu login terakhir');
            $table->string('last_login_ip')->nullable()->comment('IP login terakhir');
            $table->integer('login_attempts')->default(0)->comment('Jumlah percobaan login gagal');
            $table->timestamp('locked_until')->nullable()->comment('Akun terkunci sampai');
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('username');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_siswa');
    }
};
