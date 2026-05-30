<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah kolom password plaintext menjadi terenkripsi at-rest.
     * Kolom diperlebar ke TEXT karena ciphertext Laravel jauh lebih panjang.
     */
    public function up(): void
    {
        Schema::table('pendaftar_ppdbs', function (Blueprint $table) {
            $table->text('akun_password_plain')->nullable()->change();
        });

        // Enkripsi nilai lama yang masih plaintext.
        DB::table('pendaftar_ppdbs')
            ->whereNotNull('akun_password_plain')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('pendaftar_ppdbs')
                        ->where('id', $row->id)
                        ->update(['akun_password_plain' => Crypt::encryptString($row->akun_password_plain)]);
                }
            });
    }

    public function down(): void
    {
        // Dekripsi kembali agar reversible.
        DB::table('pendaftar_ppdbs')
            ->whereNotNull('akun_password_plain')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    try {
                        $plain = Crypt::decryptString($row->akun_password_plain);
                        DB::table('pendaftar_ppdbs')
                            ->where('id', $row->id)
                            ->update(['akun_password_plain' => $plain]);
                    } catch (\Throwable $e) {
                        // sudah plaintext, lewati
                    }
                }
            });

        Schema::table('pendaftar_ppdbs', function (Blueprint $table) {
            $table->string('akun_password_plain')->nullable()->change();
        });
    }
};
