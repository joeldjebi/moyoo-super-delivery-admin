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
        if (!Schema::hasTable('platform_admin_permissions')) {
            Schema::create('platform_admin_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('platform_admin_id')->constrained('platform_admins')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['platform_admin_id', 'permission_id']);
                $table->index('platform_admin_id');
                $table->index('permission_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_admin_permissions');
    }
};
