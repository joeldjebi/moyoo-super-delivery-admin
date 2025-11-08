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
        if (!Schema::hasTable('pricing_plan_modules')) {
            Schema::create('pricing_plan_modules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pricing_plan_id')->constrained('pricing_plans')->cascadeOnDelete();
                $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
                $table->boolean('is_enabled')->default(true);
                $table->json('limits')->nullable(); // Limites spécifiques
                $table->timestamps();

                $table->unique(['pricing_plan_id', 'module_id']);
                $table->index('pricing_plan_id');
                $table->index('module_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_plan_modules');
    }
};
