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
        if (!Schema::hasTable('admin_activity_logs')) {
            Schema::create('admin_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('platform_admin_id')->constrained('platform_admins')->cascadeOnDelete();
                $table->string('action'); // Ex: 'created', 'updated', 'deleted', 'assigned_role', etc.
                $table->string('model_type')->nullable(); // Ex: 'App\Models\PlatformAdmin', 'App\Models\Role'
                $table->unsignedBigInteger('model_id')->nullable();
                $table->text('description')->nullable();
                $table->json('old_values')->nullable(); // Anciennes valeurs (pour update)
                $table->json('new_values')->nullable(); // Nouvelles valeurs (pour update/create)
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();

                $table->index('platform_admin_id');
                $table->index(['model_type', 'model_id']);
                $table->index('action');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
