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
        Schema::create('admin_user_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ref ke user SIAKAD (tidak FK, hanya snapshot)
            $table->foreignId('admin_role_id')->constrained('admin_role')->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint untuk mencegah duplikasi
            $table->unique(['user_id', 'admin_role_id'], 'user_role_unique');
            
            // Index
            $table->index('user_id');
            $table->index('admin_role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_user_role');
    }
};