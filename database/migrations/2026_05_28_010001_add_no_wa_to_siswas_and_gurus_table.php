<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('no_wa')->nullable()->after('nisn');
        });

        Schema::table('gurus', function (Blueprint $table) {
            $table->string('no_wa')->nullable()->after('no_hp');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('no_wa');
        });

        Schema::table('gurus', function (Blueprint $table) {
            $table->dropColumn('no_wa');
        });
    }
};
