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
        if (!Schema::hasTable('platform_admin_roles')) {
            Schema::create('platform_admin_roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('platform_admin_id')->constrained('platform_admins')->cascadeOnDelete();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['platform_admin_id', 'role_id']);
                $table->index('platform_admin_id');
                $table->index('role_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_admin_roles');
    }
};
