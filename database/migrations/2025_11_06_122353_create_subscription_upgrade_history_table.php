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
        if (!Schema::hasTable('subscription_upgrade_history')) {
            Schema::create('subscription_upgrade_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('entreprise_id');
                $table->unsignedBigInteger('ancien_subscription_plan_id')->nullable();
                $table->unsignedBigInteger('nouveau_subscription_plan_id')->nullable();
                $table->unsignedBigInteger('ancien_pricing_plan_id')->nullable();
                $table->unsignedBigInteger('nouveau_pricing_plan_id')->nullable();
                $table->unsignedBigInteger('upgraded_by'); // platform_admin_id
                $table->decimal('ancien_prix', 10, 2)->nullable();
                $table->decimal('nouveau_prix', 10, 2)->nullable();
                $table->string('ancien_currency', 10)->nullable();
                $table->string('nouveau_currency', 10)->nullable();
                $table->text('raison')->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('date_upgrade')->useCurrent();
                $table->timestamps();
                $table->softDeletes();

                // Index
                $table->index('entreprise_id');
                $table->index('upgraded_by');
                $table->index('date_upgrade');

                // Foreign keys (optionnel, peut être ajouté plus tard)
                // $table->foreign('entreprise_id')->references('id')->on('entreprises');
                // $table->foreign('upgraded_by')->references('id')->on('platform_admins');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_upgrade_history');
    }
};
