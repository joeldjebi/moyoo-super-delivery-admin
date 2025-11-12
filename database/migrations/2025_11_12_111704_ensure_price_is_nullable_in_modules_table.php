<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pour PostgreSQL, modifier directement la colonne pour qu'elle soit nullable
        if (Schema::hasColumn('modules', 'price')) {
            DB::statement('ALTER TABLE modules ALTER COLUMN price DROP NOT NULL');
        }
        
        // S'assurer que currency peut aussi être null
        if (Schema::hasColumn('modules', 'currency')) {
            DB::statement('ALTER TABLE modules ALTER COLUMN currency DROP NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre les contraintes NOT NULL si nécessaire
        if (Schema::hasColumn('modules', 'price')) {
            DB::statement('ALTER TABLE modules ALTER COLUMN price SET NOT NULL');
        }
        
        if (Schema::hasColumn('modules', 'currency')) {
            DB::statement('ALTER TABLE modules ALTER COLUMN currency SET NOT NULL');
        }
    }
};
