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
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('resource'); // Ex: 'admins', 'roles', 'permissions', 'entreprises', etc.
                $table->string('action'); // Ex: 'create', 'read', 'update', 'delete', 'manage'
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index('resource');
                $table->index('action');
                $table->unique(['resource', 'action']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
