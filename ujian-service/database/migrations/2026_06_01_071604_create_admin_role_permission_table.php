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
        Schema::create('admin_role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_role_id')->constrained('admin_role')->onDelete('cascade');
            $table->foreignId('admin_permission_id')->constrained('admin_permission')->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint untuk mencegah duplikasi
            $table->unique(['admin_role_id', 'admin_permission_id'], 'role_permission_unique');
            
            // Index
            $table->index('admin_role_id');
            $table->index('admin_permission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_role_permission');
    }
};