<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('nis', 20)->nullable()->after('id');
        });

        // Isi nis dengan format 10021 + id
        DB::statement('UPDATE siswas SET nis = CONCAT(10020 + id) WHERE nis IS NULL');
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('nis');
        });
    }
};
