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
        if (!Schema::hasColumn('modules', 'price')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->nullable()->after('description');
            });
        }
        
        if (!Schema::hasColumn('modules', 'currency')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->string('currency', 3)->default('XOF')->after('price');
            });
        }
        
        if (!Schema::hasColumn('modules', 'is_optional')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->boolean('is_optional')->default(false)->after('currency');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['price', 'currency', 'is_optional']);
        });
    }
};

