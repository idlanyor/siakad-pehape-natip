<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_spps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_spp_id')->constrained('tagihan_spps')->onDelete('cascade');
            $table->date('tanggal_bayar');
            $table->decimal('nominal_bayar', 10, 2);
            $table->enum('metode', ['tunai', 'transfer', 'qris'])->default('tunai');
            $table->string('bukti')->nullable(); // path file upload
            $table->string('catatan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_spps');
    }
};
