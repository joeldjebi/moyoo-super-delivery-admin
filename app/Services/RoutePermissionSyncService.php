<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RoutePermissionSyncService
{
    /**
     * Synchroniser les routes avec les permissions
     */
    public function sync(): array
    {
        $routes = $this->extractPlatformAdminRoutes();
        $created = [];
        $updated = [];

        foreach ($routes as $routeInfo) {
            $permission = Permission::firstOrCreate(
                [
                    'resource' => $routeInfo['resource'],
                    'action' => $routeInfo['action']
                ],
                [
                    'name' => $routeInfo['name'],
                    'description' => $routeInfo['description']
                ]
            );

            // Si la permission existait déjà, mettre à jour le nom et la description
            if ($permission->wasRecentlyCreated) {
                $created[] = $permission;
            } else {
                // Mettre à jour si nécessaire
                $needsUpdate = false;
                if ($permission->name !== $routeInfo['name']) {
                    $permission->name = $routeInfo['name'];
                    $needsUpdate = true;
                }
                if ($permission->description !== $routeInfo['description']) {
                    $permission->description = $routeInfo['description'];
                    $needsUpdate = true;
                }
                if ($needsUpdate) {
                    $permission->save();
                    $updated[] = $permission;
                }
            }
        }

        // Attribuer toutes les permissions au rôle super-admin
        $this->assignToSuperAdmin();

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => count($routes)
        ];
    }

    /**
     * Extraire les routes platform-admin et les convertir en permissions
     */
    protected function extractPlatformAdminRoutes(): array
    {
        $routes = [];
        $allRoutes = Route::getRoutes();

        foreach ($allRoutes as $route) {
            // Filtrer uniquement les routes platform-admin protégées
            if (!$this->isPlatformAdminRoute($route)) {
                continue;
            }

            $routeInfo = $this->parseRouteToPermission($route);
            if ($routeInfo) {
                $routes[] = $routeInfo;
            }
        }

        return $routes;
    }

    /**
     * Vérifier si la route est une route platform-admin protégée
     */
    protected function isPlatformAdminRoute($route): bool
    {
        $uri = $route->uri();
        $name = $route->getName();

        // Vérifier le préfixe et le nom
        if (!str_starts_with($uri, 'platform-admin/')) {
            return false;
        }

        // Exclure les routes publiques (login)
        if (str_contains($uri, 'login') || str_contains($uri, 'logout')) {
            return false;
        }

        // Vérifier que c'est une route protégée (avec middleware)
        $middleware = $route->middleware();
        if (!in_array('auth:platform_admin', $middleware) && !in_array('super-admin', $middleware)) {
            return false;
        }

        return true;
    }

    /**
     * Parser une route pour extraire les informations de permission
     */
    protected function parseRouteToPermission($route): ?array
    {
        $uri = $route->uri();
        $methods = $route->methods();
        $name = $route->getName();

        // Extraire le nom de la ressource depuis l'URI ou le nom de la route
        $resource = $this->extractResource($uri, $name);
        $action = $this->extractAction($uri, $methods, $name);

        if (!$resource || !$action) {
            return null;
        }

        // Générer le nom et la description
        $name = $this->generatePermissionName($resource, $action);
        $description = $this->generatePermissionDescription($resource, $action);

        return [
            'resource' => $resource,
            'action' => $action,
            'name' => $name,
            'description' => $description,
            'route_name' => $name,
            'uri' => $uri
        ];
    }

    /**
     * Extraire le nom de la ressource depuis l'URI ou le nom de la route
     */
    protected function extractResource(string $uri, ?string $routeName): ?string
    {
        // Retirer le préfixe platform-admin/
        $uri = str_replace('platform-admin/', '', $uri);

        // Extraire depuis le nom de la route si disponible
        if ($routeName) {
            $parts = explode('.', $routeName);
            if (count($parts) >= 2) {
                // Format: platform-admin.resource.action
                return $parts[1];
            }
        }

        // Extraire depuis l'URI
        $segments = explode('/', $uri);
        $resource = $segments[0] ?? null;

        // Nettoyer le nom de la ressource
        if ($resource) {
            // Retirer les tirets et convertir en singulier si nécessaire
            $resource = str_replace('-', '_', $resource);

            // Gérer les cas spéciaux
            $specialCases = [
                'admin_users' => 'admins',
                'pricing_plans' => 'pricing_plans',
                'global_data' => 'global_data',
            ];

            return $specialCases[$resource] ?? $resource;
        }

        return null;
    }

    /**
     * Extraire l'action depuis l'URI, les méthodes HTTP et le nom de la route
     */
    protected function extractAction(string $uri, array $methods, ?string $routeName): ?string
    {
        // Extraire depuis le nom de la route si disponible
        if ($routeName) {
            $parts = explode('.', $routeName);
            if (count($parts) >= 3) {
                // Format: platform-admin.resource.action
                $action = $parts[2];

                // Mapper les actions spéciales
                $actionMap = [
                    'index' => 'read',
                    'show' => 'read',
                    'store' => 'create',
                    'update' => 'update',
                    'destroy' => 'delete',
                    'toggle-status' => 'toggle_status',
                    'assign-permissions' => 'assign_permissions',
                    'assign-roles' => 'assign_roles',
                    'remove-role' => 'remove_role',
                    'remove-permission' => 'remove_permission',
                    'upgrade-history' => 'upgrade_history',
                    'upgrade-form' => 'upgrade_form',
                    'upgrade' => 'upgrade',
                ];

                return $actionMap[$action] ?? $action;
            }
        }

        // Extraire depuis les méthodes HTTP
        if (in_array('GET', $methods)) {
            if (str_contains($uri, 'create') || str_ends_with($uri, '/create')) {
                return 'create';
            }
            if (str_contains($uri, 'edit') || str_contains($uri, '-edit')) {
                return 'update';
            }
            if (preg_match('/\{[^}]+\}/', $uri) && !str_contains($uri, 'create') && !str_contains($uri, 'edit')) {
                return 'read';
            }
            return 'read';
        }

        if (in_array('POST', $methods)) {
            if (str_contains($uri, 'assign') || str_contains($uri, 'upgrade')) {
                return str_replace('-', '_', basename($uri));
            }
            return 'create';
        }

        if (in_array('PUT', $methods) || in_array('PATCH', $methods)) {
            return 'update';
        }

        if (in_array('DELETE', $methods)) {
            return 'delete';
        }

        return 'read';
    }

    /**
     * Générer le nom de la permission
     */
    protected function generatePermissionName(string $resource, string $action): string
    {
        $resourceNames = [
            'admins' => 'administrateurs',
            'admin_users' => 'administrateurs',
            'roles' => 'rôles',
            'permissions' => 'permissions',
            'entreprises' => 'entreprises',
            'users' => 'utilisateurs',
            'subscriptions' => 'abonnements',
            'pricing_plans' => 'plans tarifaires',
            'logs' => 'logs',
            'global_data' => 'données globales',
        ];

        $actionNames = [
            'create' => 'Créer',
            'read' => 'Lire',
            'update' => 'Modifier',
            'delete' => 'Supprimer',
            'manage' => 'Gérer',
            'toggle_status' => 'Activer/Désactiver',
            'assign_permissions' => 'Attribuer des permissions',
            'assign_roles' => 'Attribuer des rôles',
            'remove_role' => 'Retirer un rôle',
            'remove_permission' => 'Retirer une permission',
            'upgrade_history' => 'Voir l\'historique des upgrades',
            'upgrade_form' => 'Voir le formulaire d\'upgrade',
            'upgrade' => 'Upgrader un abonnement',
        ];

        $resourceName = $resourceNames[$resource] ?? Str::title(str_replace('_', ' ', $resource));
        $actionName = $actionNames[$action] ?? Str::title(str_replace('_', ' ', $action));

        return "{$actionName} {$resourceName}";
    }

    /**
     * Générer la description de la permission
     */
    protected function generatePermissionDescription(string $resource, string $action): string
    {
        $descriptions = [
            'create' => 'Permet de créer',
            'read' => 'Permet de consulter',
            'update' => 'Permet de modifier',
            'delete' => 'Permet de supprimer',
            'manage' => 'Permet de gérer',
            'toggle_status' => 'Permet d\'activer ou désactiver',
            'assign_permissions' => 'Permet d\'attribuer des permissions',
            'assign_roles' => 'Permet d\'attribuer des rôles',
            'remove_role' => 'Permet de retirer un rôle',
            'remove_permission' => 'Permet de retirer une permission',
            'upgrade_history' => 'Permet de consulter l\'historique des upgrades',
            'upgrade_form' => 'Permet d\'accéder au formulaire d\'upgrade',
            'upgrade' => 'Permet d\'upgrader un abonnement',
        ];

        $actionDesc = $descriptions[$action] ?? 'Permet d\'effectuer l\'action';
        $resourceDesc = Str::plural(str_replace('_', ' ', $resource));

        return "{$actionDesc} {$resourceDesc}";
    }

    /**
     * Attribuer toutes les permissions au rôle super-admin
     */
    protected function assignToSuperAdmin(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        if (!$superAdminRole) {
            return;
        }

        $allPermissions = Permission::all();
        $superAdminRole->permissions()->sync($allPermissions->pluck('id'));
    }
}

