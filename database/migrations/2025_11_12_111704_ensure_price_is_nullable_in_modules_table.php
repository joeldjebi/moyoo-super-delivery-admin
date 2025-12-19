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
        $driver = DB::connection()->getDriverName();

        // Rendre les colonnes nullable, en utilisant la syntaxe correcte selon le SGBD.
        // - PostgreSQL: ALTER COLUMN ... DROP NOT NULL
        // - MySQL/MariaDB: MODIFY ... NULL
        if (Schema::hasColumn('modules', 'price')) {
            if ($driver === 'pgsql') {
                DB::statement('ALTER TABLE modules ALTER COLUMN price DROP NOT NULL');
            } elseif ($driver === 'mysql') {
                DB::statement('ALTER TABLE modules MODIFY price DECIMAL(10,2) NULL');
            }
        }

        if (Schema::hasColumn('modules', 'currency')) {
            if ($driver === 'pgsql') {
                DB::statement('ALTER TABLE modules ALTER COLUMN currency DROP NOT NULL');
            } elseif ($driver === 'mysql') {
                // Conserver le DEFAULT existant tout en autorisant NULL
                DB::statement("ALTER TABLE modules MODIFY currency VARCHAR(3) NULL DEFAULT 'XOF'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        // Revenir à l'état avant cette migration:
        // - `price` était déjà nullable dans 2025_11_07_120000_add_price_fields_to_modules_table.php
        // - `currency` était NOT NULL avec DEFAULT 'XOF'
        if (Schema::hasColumn('modules', 'currency')) {
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE modules ALTER COLUMN currency SET NOT NULL");
                DB::statement("ALTER TABLE modules ALTER COLUMN currency SET DEFAULT 'XOF'");
            } elseif ($driver === 'mysql') {
                DB::statement("ALTER TABLE modules MODIFY currency VARCHAR(3) NOT NULL DEFAULT 'XOF'");
            }
        }
    }
};
