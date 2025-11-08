<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;
use App\Models\PlatformAdmin;
use App\Models\Permission;

class TestSuperAdminPermissions extends Command
{
    protected $signature = 'test:super-admin-permissions';
    protected $description = 'Teste si les permissions du super admin s\'affichent correctement';

    public function handle()
    {
        $this->info('🧪 Test des permissions du super admin...');
        $this->newLine();

        // 1. Vérifier si le rôle super-admin existe
        $this->info('1. Vérification du rôle super-admin:');
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        if (!$superAdminRole) {
            $this->error('   ❌ Le rôle super-admin n\'existe pas !');
            return 1;
        }

        $this->info("   ✅ Rôle trouvé: {$superAdminRole->name} (ID: {$superAdminRole->id})");
        $this->newLine();

        // 2. Vérifier les permissions du rôle super-admin
        $this->info('2. Vérification des permissions du rôle super-admin:');

        // Utiliser notre méthode avec fallback
        $permissions = $superAdminRole->getPermissionsWithFallback();
        $this->info("   Permissions via méthode fallback: {$permissions->count()}");

        // Vérifier directement en DB
        $hasRoleId = Schema::hasColumn('role_permissions', 'role_id');
        if ($hasRoleId) {
            $dbCount = DB::table('role_permissions')
                ->where('role_id', $superAdminRole->id)
                ->count();
            $this->info("   Permissions en DB (via role_id): {$dbCount}");
        } else {
            $dbCount = DB::table('role_permissions')
                ->where('role', 'super-admin')
                ->orWhere('role', $superAdminRole->name)
                ->count();
            $this->info("   Permissions en DB (via role): {$dbCount}");
        }

        if ($permissions->count() > 0) {
            $this->info('   ✅ Les permissions sont chargées correctement');
            $this->line('   Premières permissions:');
            foreach ($permissions->take(5) as $perm) {
                $this->line("     - {$perm->resource}.{$perm->action} ({$perm->name})");
            }
        } else {
            $this->warn('   ⚠️  Aucune permission trouvée pour le rôle super-admin');
        }
        $this->newLine();

        // 3. Vérifier si un super admin existe
        $this->info('3. Vérification des super admins:');
        $superAdmins = PlatformAdmin::whereHas('roles', function ($query) {
            $query->where('slug', 'super-admin');
        })->get();

        if ($superAdmins->count() === 0) {
            $this->warn('   ⚠️  Aucun super admin trouvé');
        } else {
            $this->info("   ✅ {$superAdmins->count()} super admin(s) trouvé(s)");

            foreach ($superAdmins as $admin) {
                $this->line("     - {$admin->username} (ID: {$admin->id})");

                // Tester la méthode getAllPermissions
                $allPerms = $admin->getAllPermissions();
                $this->line("       Permissions totales: {$allPerms->count()}");
            }
        }
        $this->newLine();

        // 4. Test de la relation dans le contrôleur
        $this->info('4. Test de la relation Role->permissions:');
        $testRole = Role::with('permissions')->find($superAdminRole->id);

        if ($testRole) {
            $relCount = $testRole->permissions->count();
            $this->info("   Permissions via relation: {$relCount}");

            if ($relCount === 0) {
                $this->warn('   ⚠️  La relation ne fonctionne pas, le fallback sera utilisé');
            } else {
                $this->info('   ✅ La relation fonctionne correctement');
            }
        }
        $this->newLine();

        // 5. Résumé
        $this->info('5. Résumé:');
        if ($permissions->count() > 0) {
            $this->info('   ✅ Les permissions du super admin DEVRAIENT s\'afficher');
        } else {
            $this->error('   ❌ Les permissions du super admin NE s\'afficheront PAS');
            $this->line('   → Vérifiez que le rôle super-admin a des permissions assignées dans role_permissions');
        }

        return 0;
    }
}

