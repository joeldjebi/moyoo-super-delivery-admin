# Synchronisation Automatique des Routes avec les Permissions

## Vue d'ensemble

Ce système permet de synchroniser automatiquement les routes `platform-admin` avec les permissions RBAC. Lorsqu'une nouvelle route est créée, elle est automatiquement détectée, une permission correspondante est créée, et cette permission est attribuée au rôle `super-admin`.

## Fonctionnement

### 1. Service de Synchronisation

Le service `RoutePermissionSyncService` :
- Extrait toutes les routes `platform-admin` protégées
- Parse chaque route pour extraire la ressource et l'action
- Crée ou met à jour les permissions correspondantes
- Attribue automatiquement toutes les permissions au rôle `super-admin`

### 2. Structure des Permissions

Les permissions sont structurées comme suit :
- **Resource** : Nom de la ressource (ex: `roles`, `admins`, `entreprises`)
- **Action** : Action effectuée (ex: `create`, `read`, `update`, `delete`)
- **Name** : Nom lisible de la permission (ex: "Créer des rôles")
- **Description** : Description de la permission

### 3. Mapping des Routes vers les Permissions

Le système mappe automatiquement :
- `GET /platform-admin/roles` → `roles.read`
- `GET /platform-admin/roles/create` → `roles.create`
- `POST /platform-admin/roles` → `roles.create`
- `GET /platform-admin/roles/{id}` → `roles.read`
- `GET /platform-admin/roles/{id}/edit` → `roles.update`
- `PUT /platform-admin/roles/{id}` → `roles.update`
- `DELETE /platform-admin/roles/{id}` → `roles.delete`

## Utilisation

### Synchronisation Manuelle

Pour synchroniser manuellement les routes avec les permissions :

```bash
php artisan permissions:sync-routes
```

Cette commande :
1. Analyse toutes les routes `platform-admin`
2. Crée les permissions manquantes
3. Met à jour les permissions existantes si nécessaire
4. Attribue toutes les permissions au rôle `super-admin`

### Synchronisation Automatique lors du Seeding

Lors de l'exécution du seeder RBAC :

```bash
php artisan db:seed --class=RBACSeeder
```

Le seeder appelle automatiquement le service de synchronisation pour créer toutes les permissions à partir des routes.

## Ajout de Nouvelles Routes

Lorsque vous ajoutez une nouvelle route dans `routes/platform-admin.php` :

1. **Définir la route** :
```php
Route::get('nouvelle-ressource', [NouveauController::class, 'index'])->name('nouvelle-ressource.index');
```

2. **Synchroniser les permissions** :
```bash
php artisan permissions:sync-routes
```

3. **Vérifier** : La nouvelle permission sera automatiquement créée et attribuée au rôle `super-admin`.

## Format des Routes

Pour que le système détecte correctement vos routes, elles doivent :

1. **Avoir le préfixe `platform-admin/`**
2. **Être protégées par les middlewares** `auth:platform_admin` ou `super-admin`
3. **Avoir un nom de route** au format `platform-admin.resource.action`

### Exemples de Routes Correctes

```php
// Route simple
Route::get('roles', [RoleController::class, 'index'])->name('roles.index');

// Route avec paramètre
Route::get('roles/{id}', [RoleController::class, 'show'])->name('roles.show');

// Route avec action personnalisée
Route::post('roles/{id}/assign-permissions', [RoleController::class, 'assignPermissions'])
    ->name('roles.assign-permissions');
```

## Actions Personnalisées

Le système détecte automatiquement les actions personnalisées :
- `toggle-status` → `toggle_status`
- `assign-permissions` → `assign_permissions`
- `assign-roles` → `assign_roles`
- `remove-role` → `remove_role`
- `upgrade-history` → `upgrade_history`

## Vérification

Pour vérifier que les permissions ont été créées :

```bash
php artisan tinker
```

```php
use App\Models\Permission;
use App\Models\Role;

// Voir toutes les permissions
Permission::all();

// Voir les permissions du rôle super-admin
$superAdmin = Role::where('slug', 'super-admin')->first();
$superAdmin->permissions;
```

## Notes Importantes

1. **Le rôle super-admin a automatiquement toutes les permissions** : Toutes les permissions créées sont automatiquement attribuées au rôle `super-admin`.

2. **Les permissions existantes sont mises à jour** : Si une permission existe déjà, son nom et sa description sont mis à jour si nécessaire.

3. **Les routes publiques sont ignorées** : Les routes de login/logout ne sont pas synchronisées.

4. **Ordre des routes** : Les routes spécifiques doivent être définies avant les routes avec paramètres pour éviter les conflits.

## Dépannage

### Les nouvelles routes ne sont pas détectées

1. Vérifiez que la route a le préfixe `platform-admin/`
2. Vérifiez que la route est protégée par les middlewares appropriés
3. Videz le cache des routes : `php artisan route:clear`
4. Relancez la synchronisation : `php artisan permissions:sync-routes`

### Les permissions ne sont pas attribuées au super-admin

1. Vérifiez que le rôle `super-admin` existe
2. Relancez la synchronisation : `php artisan permissions:sync-routes`
3. Vérifiez manuellement dans la base de données

