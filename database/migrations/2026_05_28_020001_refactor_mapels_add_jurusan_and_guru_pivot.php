<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah jurusan_id (nullable = mapel umum semua jurusan)
        Schema::table('mapels', function (Blueprint $table) {
            $table->foreignId('jurusan_id')->nullable()->after('nama_mapel')->constrained()->nullOnDelete();
            $table->dropColumn('dosen_pengampu');
        });

        // Pivot guru_mapel: relasi many-to-many guru <-> mapel
        Schema::create('guru_mapel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['guru_id', 'mapel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_mapel');

        Schema::table('mapels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jurusan_id');
            $table->string('dosen_pengampu')->nullable()->after('nama_mapel');
        });
    }
};
