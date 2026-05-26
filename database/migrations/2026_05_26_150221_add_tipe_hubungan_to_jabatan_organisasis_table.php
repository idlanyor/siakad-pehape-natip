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
        Schema::table('jabatan_organisasis', function (Blueprint $table) {
            // Tipe garis dari node ini ke parent-nya (atau ke node sejajar via koordinator_ids)
            // komando = garis solid, koordinasi = garis putus-putus, konsultasi = garis titik-titik
            $table->enum('tipe_hubungan', ['komando', 'koordinasi', 'konsultasi'])->default('komando')->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('jabatan_organisasis', function (Blueprint $table) {
            $table->dropColumn('tipe_hubungan');
        });
    }
};
