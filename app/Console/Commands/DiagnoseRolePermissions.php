<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;

class DiagnoseRolePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnose:role-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnostique les problèmes de la table role_permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Diagnostic de la table role_permissions...');
        $this->newLine();

        // Vérifier la structure de la table
        $this->info('1. Structure de la table:');
        $columns = DB::select("
            SELECT column_name, data_type, is_nullable
            FROM information_schema.columns
            WHERE table_name = 'role_permissions'
            ORDER BY ordinal_position
        ");

        $hasRoleId = false;
        $hasRole = false;
        $hasPermissionId = false;

        foreach ($columns as $column) {
            $marker = '';
            if ($column->column_name === 'role_id' && $column->data_type === 'bigint') {
                $marker = ' ✅';
                $hasRoleId = true;
            } elseif ($column->column_name === 'role') {
                $marker = ' ⚠️  (devrait être role_id)';
                $hasRole = true;
            } elseif ($column->column_name === 'permission_id' && $column->data_type === 'bigint') {
                $marker = ' ✅';
                $hasPermissionId = true;
            } elseif ($column->column_name === 'permission_id' && ($column->data_type === 'json' || $column->data_type === 'jsonb')) {
                $marker = ' ❌ (devrait être bigint)';
            }

            $this->line("   - {$column->column_name} ({$column->data_type}){$marker}");
        }

        $this->newLine();

        // Vérifier les données
        $this->info('2. Données dans la table:');
        $totalRows = DB::selectOne("SELECT COUNT(*) as count FROM role_permissions");
        $this->line("   Total de lignes: {$totalRows->count}");

        if ($hasRole) {
            $roleValues = DB::select("SELECT DISTINCT role FROM role_permissions LIMIT 10");
            $this->line("   Valeurs dans la colonne 'role':");
            foreach ($roleValues as $val) {
                $this->line("     - '{$val->role}'");
            }
        }

        if ($hasRoleId) {
            $roleIdCount = DB::selectOne("SELECT COUNT(*) as count FROM role_permissions WHERE role_id IS NOT NULL");
            $this->line("   Lignes avec role_id: {$roleIdCount->count}");
        }

        $this->newLine();

        // Tester la relation Laravel
        $this->info('3. Test de la relation Laravel:');
        $roles = Role::with('permissions')->limit(3)->get();

        foreach ($roles as $role) {
            $permissionCount = $role->permissions->count();
            $this->line("   Rôle '{$role->name}' (ID: {$role->id}): {$permissionCount} permission(s)");

            if ($permissionCount === 0) {
                // Vérifier si des données existent dans la table
                if ($hasRole) {
                    $dbCount = DB::selectOne("
                        SELECT COUNT(*) as count
                        FROM role_permissions
                        WHERE role = ? OR role = ?
                    ", [$role->slug, $role->name]);
                } elseif ($hasRoleId) {
                    $dbCount = DB::selectOne("
                        SELECT COUNT(*) as count
                        FROM role_permissions
                        WHERE role_id = ?
                    ", [$role->id]);
                } else {
                    $dbCount = (object)['count' => 0];
                }

                if ($dbCount->count > 0) {
                    $this->warn("     ⚠️  {$dbCount->count} ligne(s) trouvée(s) dans la table mais la relation ne fonctionne pas !");
                }
            }
        }

        $this->newLine();

        // Recommandations
        $this->info('4. Recommandations:');
        if (!$hasRoleId && $hasRole) {
            $this->error('   ❌ La colonne "role" doit être convertie en "role_id"');
            $this->line('   → Exécutez: php artisan fix:role-permissions-structure');
        } elseif (!$hasRoleId && !$hasRole) {
            $this->error('   ❌ La colonne "role_id" est manquante');
            $this->line('   → La table doit être recréée ou la colonne doit être ajoutée');
        } elseif (!$hasPermissionId) {
            $this->error('   ❌ La colonne "permission_id" n\'est pas du bon type');
            $this->line('   → Exécutez: php artisan fix:role-permissions-column');
        } else {
            $this->info('   ✅ La structure semble correcte');
        }

        return 0;
    }
}

