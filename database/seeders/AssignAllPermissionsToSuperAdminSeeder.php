<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\PlatformAdmin;
use App\Models\Role;
use App\Models\Permission;

class AssignAllPermissionsToSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔧 Attribution de toutes les permissions au premier super admin...');
        $this->command->newLine();

        // 1. Trouver le premier super admin
        $firstSuperAdmin = PlatformAdmin::getFirstSuperAdmin();

        if (!$firstSuperAdmin) {
            // Si aucun premier super admin n'existe, prendre le premier admin créé
            $firstSuperAdmin = PlatformAdmin::orderBy('id', 'asc')->first();
            
            if (!$firstSuperAdmin) {
                $this->command->error('❌ Aucun administrateur trouvé !');
                $this->command->warn('💡 Exécutez d\'abord: php artisan db:seed --class=PlatformAdminSeeder');
                return;
            }
            
            $this->command->warn("⚠️  Premier super admin non trouvé, utilisation du premier admin (ID: {$firstSuperAdmin->id})");
        } else {
            $this->command->info("✅ Premier super admin trouvé: {$firstSuperAdmin->username} (ID: {$firstSuperAdmin->id})");
        }

        // 2. S'assurer que le premier admin a le rôle super-admin
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        if (!$superAdminRole) {
            $this->command->error('❌ Le rôle super-admin n\'existe pas !');
            $this->command->warn('💡 Exécutez d\'abord: php artisan db:seed --class=RBACSeeder');
            return;
        }

        // Vérifier si l'admin a déjà le rôle
        $hasRole = $firstSuperAdmin->roles()->where('roles.id', $superAdminRole->id)->exists();
        
        if (!$hasRole) {
            $firstSuperAdmin->roles()->attach($superAdminRole->id);
            $this->command->info("✅ Rôle super-admin assigné à {$firstSuperAdmin->username}");
        } else {
            $this->command->info("✅ {$firstSuperAdmin->username} a déjà le rôle super-admin");
        }

        // 3. Récupérer toutes les permissions
        $permissions = Permission::all();
        $this->command->info("✅ {$permissions->count()} permission(s) trouvée(s)");

        if ($permissions->count() === 0) {
            $this->command->warn('⚠️  Aucune permission disponible');
            $this->command->warn('💡 Exécutez d\'abord: php artisan db:seed --class=RBACSeeder');
            return;
        }

        // 4. Vérifier la structure de la table role_permissions
        $hasRoleId = Schema::hasColumn('role_permissions', 'role_id');
        $hasRole = Schema::hasColumn('role_permissions', 'role');

        if (!$hasRoleId && !$hasRole) {
            $this->command->error('❌ La table role_permissions n\'a ni role_id ni role !');
            return;
        }

        // 5. Assigner toutes les permissions au rôle super-admin
        $this->command->info('🔄 Attribution des permissions au rôle super-admin...');
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
                    'role' => $superAdminRole->slug,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $assigned++;
        }

        $this->command->newLine();
        $this->command->info("✅ {$assigned} permission(s) assignée(s) au rôle super-admin");
        if ($skipped > 0) {
            $this->command->info("⏭️  {$skipped} permission(s) déjà existante(s) (ignorée(s))");
        }

        // 6. Vérifier le résultat
        $this->command->newLine();
        $this->command->info('🧪 Vérification du résultat...');

        if ($hasRoleId) {
            $finalCount = DB::table('role_permissions')
                ->where('role_id', $superAdminRole->id)
                ->count();
        } else {
            $finalCount = DB::table('role_permissions')
                ->where(function($query) use ($superAdminRole) {
                    $query->where('role', 'super-admin')
                          ->orWhere('role', $superAdminRole->name);
                })
                ->count();
        }

        $this->command->info("📊 Total de permissions pour le rôle super-admin: {$finalCount}");

        // Tester la relation (utiliser toujours getPermissionsWithFallback pour éviter les erreurs)
        $testRole = Role::find($superAdminRole->id);
        $fallbackCount = $testRole->getPermissionsWithFallback()->count();
        $this->command->info("📊 Permissions via fallback: {$fallbackCount}");

        if ($finalCount > 0) {
            $this->command->info('✅ Le premier super admin a maintenant tous les droits !');
        }

        $this->command->newLine();
        $this->command->info('✅ Seeder terminé avec succès !');
    }
}

