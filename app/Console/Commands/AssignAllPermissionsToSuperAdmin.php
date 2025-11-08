<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;
use App\Models\Permission;

class AssignAllPermissionsToSuperAdmin extends Command
{
    protected $signature = 'assign:all-permissions-to-super-admin';
    protected $description = 'Assigne toutes les permissions au rôle super-admin';

    public function handle()
    {
        $this->info('🔧 Attribution de toutes les permissions au rôle super-admin...');
        $this->newLine();

        // 1. Trouver le rôle super-admin
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        if (!$superAdminRole) {
            $this->error('❌ Le rôle super-admin n\'existe pas !');
            return 1;
        }

        $this->info("✅ Rôle trouvé: {$superAdminRole->name} (ID: {$superAdminRole->id})");

        // 2. Récupérer toutes les permissions
        $permissions = Permission::all();
        $this->info("✅ {$permissions->count()} permission(s) trouvée(s)");

        if ($permissions->count() === 0) {
            $this->warn('⚠️  Aucune permission disponible');
            return 0;
        }

        // 3. Vérifier la structure de la table
        $hasRoleId = Schema::hasColumn('role_permissions', 'role_id');
        $hasRole = Schema::hasColumn('role_permissions', 'role');

        if (!$hasRoleId && !$hasRole) {
            $this->error('❌ La table role_permissions n\'a ni role_id ni role !');
            return 1;
        }

        // 4. Vérifier les permissions existantes
        if ($hasRoleId) {
            $existingCount = DB::table('role_permissions')
                ->where('role_id', $superAdminRole->id)
                ->count();
        } else {
            $existingCount = DB::table('role_permissions')
                ->where('role', 'super-admin')
                ->orWhere('role', $superAdminRole->name)
                ->count();
        }

        $this->info("📊 Permissions existantes pour ce rôle: {$existingCount}");

        // 5. Assigner toutes les permissions
        $this->info('🔄 Attribution des permissions...');
        $assigned = 0;
        $skipped = 0;

        foreach ($permissions as $permission) {
            // Vérifier si la permission existe déjà
            if ($hasRoleId) {
                $exists = DB::table('role_permissions')
                    ->where('role_id', $superAdminRole->id)
                    ->where('permission_id', $permission->id)
                    ->exists();
            } else {
                $exists = DB::table('role_permissions')
                    ->where(function($query) use ($superAdminRole) {
                        $query->where('role', 'super-admin')
                              ->orWhere('role', $superAdminRole->name);
                    })
                    ->where('permission_id', $permission->id)
                    ->exists();
            }

            if ($exists) {
                $skipped++;
                continue;
            }

            // Insérer la permission
            if ($hasRoleId) {
                DB::table('role_permissions')->insert([
                    'role_id' => $superAdminRole->id,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('role_permissions')->insert([
                    'role' => 'super-admin',
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $assigned++;
        }

        $this->newLine();
        $this->info("✅ {$assigned} permission(s) assignée(s)");
        if ($skipped > 0) {
            $this->info("⏭️  {$skipped} permission(s) déjà existante(s) (ignorée(s))");
        }

        // 6. Vérifier le résultat
        $this->newLine();
        $this->info('🧪 Vérification du résultat...');

        if ($hasRoleId) {
            $finalCount = DB::table('role_permissions')
                ->where('role_id', $superAdminRole->id)
                ->count();
        } else {
            $finalCount = DB::table('role_permissions')
                ->where('role', 'super-admin')
                ->orWhere('role', $superAdminRole->name)
                ->count();
        }

        $this->info("📊 Total de permissions pour le rôle super-admin: {$finalCount}");

        // Tester la relation
        $testRole = Role::with('permissions')->find($superAdminRole->id);
        $relCount = $testRole->permissions->count();

        if ($relCount === 0) {
            // Utiliser le fallback
            $fallbackCount = $testRole->getPermissionsWithFallback()->count();
            $this->info("📊 Permissions via fallback: {$fallbackCount}");
        } else {
            $this->info("📊 Permissions via relation: {$relCount}");
        }

        if ($finalCount > 0) {
            $this->info('✅ Les permissions devraient maintenant s\'afficher dans la vue !');
        }

        return 0;
    }
}

