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
        if (Schema::hasTable('subscription_upgrade_history')) {
            Schema::table('subscription_upgrade_history', function (Blueprint $table) {
                if (!Schema::hasColumn('subscription_upgrade_history', 'document')) {
                    $table->string('document')->nullable()->after('notes');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('subscription_upgrade_history')) {
            Schema::table('subscription_upgrade_history', function (Blueprint $table) {
                if (Schema::hasColumn('subscription_upgrade_history', 'document')) {
                    $table->dropColumn('document');
                }
            });
        }
    }
};
