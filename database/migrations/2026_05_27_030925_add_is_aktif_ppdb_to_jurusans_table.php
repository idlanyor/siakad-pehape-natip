<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('jurusans', 'is_aktif_ppdb')) {
            Schema::table('jurusans', function (Blueprint $table) {
                $table->boolean('is_aktif_ppdb')->default(true)->after('icon');
            });
        }
        DB::table('jurusans')->update(['is_aktif_ppdb' => true]);
    }

    public function down(): void
    {
        Schema::table('jurusans', function (Blueprint $table) {
            $table->dropColumn('is_aktif_ppdb');
        });
    }
};
