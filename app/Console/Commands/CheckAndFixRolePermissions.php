<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;

class CheckAndFixRolePermissions extends Command
{
    protected $signature = 'fix:check-role-permissions';
    protected $description = 'Vérifie et corrige la structure de role_permissions';

    public function handle()
    {
        $this->info('🔍 Vérification de la structure...');

        // Vérifier les colonnes
        $columns = DB::select("
            SELECT column_name, data_type
            FROM information_schema.columns
            WHERE table_name = 'role_permissions'
            ORDER BY ordinal_position
        ");

        $this->table(['Colonne', 'Type'], array_map(function($col) {
            return [$col->column_name, $col->data_type];
        }, $columns));

        $hasRoleId = Schema::hasColumn('role_permissions', 'role_id');
        $hasRole = Schema::hasColumn('role_permissions', 'role');

        if (!$hasRoleId && $hasRole) {
            $this->warn('⚠️  La colonne "role" existe mais pas "role_id"');
            $this->info('Correction en cours...');

            // Ajouter role_id
            DB::statement('ALTER TABLE role_permissions ADD COLUMN IF NOT EXISTS role_id bigint');

            // Remplir role_id depuis role
            $this->info('Conversion des valeurs de "role" en "role_id"...');
            $updated = DB::statement('
                UPDATE role_permissions rp
                SET role_id = r.id
                FROM roles r
                WHERE (r.slug = rp.role OR r.name = rp.role)
                AND rp.role_id IS NULL
            ');

            $count = DB::selectOne("SELECT COUNT(*) as count FROM role_permissions WHERE role_id IS NOT NULL");
            $this->info("✅ {$count->count} lignes mises à jour");

            // Supprimer la colonne role si tout est converti
            $nullCount = DB::selectOne("SELECT COUNT(*) as count FROM role_permissions WHERE role_id IS NULL");
            if ($nullCount->count == 0) {
                DB::statement('ALTER TABLE role_permissions DROP COLUMN IF EXISTS role');
                $this->info('✅ Colonne "role" supprimée');
            }
        }

        // Vérifier le type de role_id
        if ($hasRoleId || Schema::hasColumn('role_permissions', 'role_id')) {
            $roleIdType = DB::selectOne("
                SELECT data_type
                FROM information_schema.columns
                WHERE table_name = 'role_permissions'
                AND column_name = 'role_id'
            ");

            if ($roleIdType && $roleIdType->data_type !== 'bigint') {
                $this->info('Conversion de role_id en bigint...');
                DB::statement('ALTER TABLE role_permissions ALTER COLUMN role_id TYPE bigint USING role_id::bigint');
            }
        }

        // Vérifier permission_id
        $permIdType = DB::selectOne("
            SELECT data_type
            FROM information_schema.columns
            WHERE table_name = 'role_permissions'
            AND column_name = 'permission_id'
        ");

        if ($permIdType && ($permIdType->data_type === 'json' || $permIdType->data_type === 'jsonb')) {
            $this->warn('⚠️  permission_id est encore JSON');
            $this->info('Exécutez: php artisan fix:role-permissions-column');
        }

        // Tester la relation
        $this->newLine();
        $this->info('🧪 Test de la relation Laravel:');
        $roles = Role::with('permissions')->limit(5)->get();

        foreach ($roles as $role) {
            $count = $role->permissions->count();
            $dbCount = DB::selectOne("
                SELECT COUNT(*) as count
                FROM role_permissions
                WHERE role_id = ?
            ", [$role->id]);

            $status = $count > 0 ? '✅' : ($dbCount->count > 0 ? '⚠️' : '❌');
            $this->line("{$status} Rôle '{$role->name}' (ID: {$role->id}): {$count} via relation, {$dbCount->count} en DB");
        }

        return 0;
    }
}

