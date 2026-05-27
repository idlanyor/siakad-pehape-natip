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
        Schema::table('pendaftar_ppdbs', function (Blueprint $table) {
            // Ganti kolom gelombang (smallInt) menjadi gelombang_id (FK) + keep gelombang lama sbg nullable
            $table->foreignId('gelombang_id')
                  ->nullable()
                  ->after('gelombang')
                  ->constrained('gelombang_ppdbs')
                  ->nullOnDelete();
            // Simpan credentials siswa baru agar admin bisa lihat kembali
            $table->string('akun_username')->nullable()->after('diproses_at');
            $table->string('akun_password_plain')->nullable()->after('akun_username')
                  ->comment('Password plain text satu kali — hapus setelah diserahkan ke siswa');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftar_ppdbs', function (Blueprint $table) {
            $table->dropForeign(['gelombang_id']);
            $table->dropColumn(['gelombang_id', 'akun_username', 'akun_password_plain']);
        });
    }
};
