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
        Schema::create('jabatan_organisasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan', 150);
            $table->foreignId('parent_id')->nullable()->constrained('jabatan_organisasis')->nullOnDelete();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jabatan_organisasis');
    }
};
