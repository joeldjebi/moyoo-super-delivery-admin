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
        Schema::table('platform_admins', function (Blueprint $table) {
            if (!Schema::hasColumn('platform_admins', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('locked_until')->constrained('platform_admins')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_admins', function (Blueprint $table) {
            if (Schema::hasColumn('platform_admins', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
