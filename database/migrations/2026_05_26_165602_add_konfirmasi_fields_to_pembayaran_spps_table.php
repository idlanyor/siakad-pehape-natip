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
        Schema::table('pembayaran_spps', function (Blueprint $table) {
            // Status konfirmasi: pending (siswa upload, belum dikonfirmasi), confirmed (admin setuju)
            $table->enum('status_konfirmasi', ['confirmed', 'pending'])->default('confirmed')->after('catatan');
            $table->foreignId('dikonfirmasi_oleh')->nullable()->after('status_konfirmasi')->constrained('users')->nullOnDelete();
            $table->timestamp('dikonfirmasi_at')->nullable()->after('dikonfirmasi_oleh');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran_spps', function (Blueprint $table) {
            $table->dropForeign(['dikonfirmasi_oleh']);
            $table->dropColumn(['status_konfirmasi', 'dikonfirmasi_oleh', 'dikonfirmasi_at']);
        });
    }
};
