<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PlatformAdmin;
use App\Models\Role;
use App\Services\RoutePermissionSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RBACSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les permissions par défaut (pour compatibilité)
        $permissions = [
            // Permissions pour les admins
            ['name' => 'Créer des administrateurs', 'resource' => 'admins', 'action' => 'create', 'description' => 'Permet de créer de nouveaux administrateurs'],
            ['name' => 'Lire les administrateurs', 'resource' => 'admins', 'action' => 'read', 'description' => 'Permet de consulter la liste des administrateurs'],
            ['name' => 'Modifier les administrateurs', 'resource' => 'admins', 'action' => 'update', 'description' => 'Permet de modifier les administrateurs'],
            ['name' => 'Supprimer les administrateurs', 'resource' => 'admins', 'action' => 'delete', 'description' => 'Permet de supprimer les administrateurs'],

            // Permissions pour les rôles
            ['name' => 'Créer des rôles', 'resource' => 'roles', 'action' => 'create', 'description' => 'Permet de créer de nouveaux rôles'],
            ['name' => 'Lire les rôles', 'resource' => 'roles', 'action' => 'read', 'description' => 'Permet de consulter la liste des rôles'],
            ['name' => 'Modifier les rôles', 'resource' => 'roles', 'action' => 'update', 'description' => 'Permet de modifier les rôles'],
            ['name' => 'Supprimer les rôles', 'resource' => 'roles', 'action' => 'delete', 'description' => 'Permet de supprimer les rôles'],

            // Permissions pour les permissions
            ['name' => 'Lire les permissions', 'resource' => 'permissions', 'action' => 'read', 'description' => 'Permet de consulter la liste des permissions'],

            // Permissions pour les entreprises
            ['name' => 'Gérer les entreprises', 'resource' => 'entreprises', 'action' => 'manage', 'description' => 'Permet de gérer toutes les entreprises'],
            ['name' => 'Lire les entreprises', 'resource' => 'entreprises', 'action' => 'read', 'description' => 'Permet de consulter les entreprises'],

            // Permissions pour les utilisateurs
            ['name' => 'Gérer les utilisateurs', 'resource' => 'users', 'action' => 'manage', 'description' => 'Permet de gérer tous les utilisateurs'],
            ['name' => 'Lire les utilisateurs', 'resource' => 'users', 'action' => 'read', 'description' => 'Permet de consulter les utilisateurs'],

            // Permissions pour les abonnements
            ['name' => 'Gérer les abonnements', 'resource' => 'subscriptions', 'action' => 'manage', 'description' => 'Permet de gérer les abonnements'],
            ['name' => 'Lire les abonnements', 'resource' => 'subscriptions', 'action' => 'read', 'description' => 'Permet de consulter les abonnements'],

            // Permissions pour les logs
            ['name' => 'Lire les logs', 'resource' => 'logs', 'action' => 'read', 'description' => 'Permet de consulter les logs système'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['resource' => $permissionData['resource'], 'action' => $permissionData['action']],
                $permissionData
            );
        }

        // Créer les rôles par défaut
        $roles = [
            [
                'name' => 'Super Administrateur',
                'slug' => 'super-admin',
                'description' => 'Accès complet à toutes les fonctionnalités',
                'is_system_role' => true,
            ],
            [
                'name' => 'Administrateur',
                'slug' => 'admin',
                'description' => 'Administrateur avec droits étendus',
                'is_system_role' => false,
            ],
            [
                'name' => 'Gestionnaire',
                'slug' => 'manager',
                'description' => 'Gestionnaire avec droits limités',
                'is_system_role' => false,
            ],
            [
                'name' => 'Employé',
                'slug' => 'employee',
                'description' => 'Employé avec droits de consultation',
                'is_system_role' => false,
            ],
        ];

        $createdRoles = [];
        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
            $createdRoles[$roleData['slug']] = $role;
        }

        // Synchroniser les routes avec les permissions
        $this->command->info('Synchronisation des routes avec les permissions...');
        $syncService = app(RoutePermissionSyncService::class);
        $syncResult = $syncService->sync();
        $this->command->info("✓ {$syncResult['total']} routes synchronisées, " . count($syncResult['created']) . " nouvelles permissions créées.");

        // Attribuer toutes les permissions au rôle super-admin
        $superAdminRole = $createdRoles['super-admin'];
        $allPermissions = Permission::all();
        $superAdminRole->permissions()->sync($allPermissions->pluck('id'));
        $this->command->info("✓ Toutes les permissions attribuées au rôle super-admin.");

        // Créer ou mettre à jour le super admin
        $superAdmin = PlatformAdmin::firstOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@example.com',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('Admin123!'),
                'status' => 'active',
                'created_by' => null, // Premier admin, créé par le système
            ]
        );

        // Attribuer le rôle super-admin au super admin
        if (!$superAdmin->roles()->where('slug', 'super-admin')->exists()) {
            $superAdmin->roles()->attach($superAdminRole->id);
        }

        // Attribuer le rôle super-admin à l'utilisateur "cooper" s'il existe
        $cooperAdmin = PlatformAdmin::where('username', 'cooper')->first();
        if ($cooperAdmin && !$cooperAdmin->roles()->where('slug', 'super-admin')->exists()) {
            $cooperAdmin->roles()->attach($superAdminRole->id);
            $this->command->info('Rôle super-admin attribué à l\'utilisateur "cooper".');
        }

        $this->command->info('RBAC initialisé avec succès !');
        $this->command->info('Super admin créé :');
        $this->command->info('  Username: admin');
        $this->command->info('  Email: admin@example.com');
        $this->command->info('  Password: Admin123!');
    }
}
