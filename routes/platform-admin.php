<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Platform Admin Routes
|--------------------------------------------------------------------------
|
| Routes pour la plateforme d'administration des super administrateurs.
| Toutes les routes sont préfixées par /platform-admin
|
*/

Route::prefix('platform-admin')->name('platform-admin.')->group(function () {
    // Routes d'authentification (publiques)
    Route::get('/login', [App\Http\Controllers\PlatformAdmin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [App\Http\Controllers\PlatformAdmin\AuthController::class, 'login'])->name('login.post');

    // Routes protégées (nécessitent authentification)
    Route::middleware(['auth:platform_admin', 'super-admin'])->group(function () {
        // Déconnexion
        Route::post('/logout', [App\Http\Controllers\PlatformAdmin\AuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\PlatformAdmin\DashboardController::class, 'index'])->name('dashboard');

        // Gestion des entreprises
        Route::get('entreprises', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'index'])->name('entreprises.index');
        Route::get('entreprises/{id}', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'show'])->name('entreprises.show');
        Route::get('entreprises-{id}-users', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'users'])->name('entreprises.users');
        Route::post('entreprises/{id}/toggle-status', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'toggleStatus'])->name('entreprises.toggle-status');

        // Sections détaillées des entreprises
        // Routes plus spécifiques en premier pour éviter les conflits
        Route::get('entreprises-{id}-marchands-{marchand_id}-ramassages-{ramassage_id}', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'showRamassage'])->name('entreprises.marchand.ramassage.show');
        Route::get('entreprises-{id}-marchands-{marchand_id}-ramassages', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'marchandRamassages'])->name('entreprises.marchand.ramassages');
        Route::get('entreprises-{id}-marchands-{marchand_id}-colis', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'marchandColis'])->name('entreprises.marchand.colis');
        Route::get('entreprises-{id}-marchands-{marchand_id}-livraisons-{livraison_id}', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'showLivraison'])->name('entreprises.marchand.livraison.show');
        Route::get('entreprises-{id}-marchands-{marchand_id}-livraisons', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'marchandLivraisons'])->name('entreprises.marchand.livraisons');
        Route::get('entreprises-{id}-marchands-{marchand_id}', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'showMarchand'])->name('entreprises.marchand.show');
        Route::get('entreprises-{id}-marchands-{marchand_id}-boutiques-{boutique_id}', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'showBoutique'])->name('entreprises.marchand.boutique.show');
        Route::get('entreprises-{id}-boutiques-{boutique_id}-colis-{colis_id}', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'showBoutiqueColis'])->name('entreprises.boutique.colis.show');
        Route::get('entreprises-{id}-boutiques-{boutique_id}-colis', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'boutiqueColis'])->name('entreprises.boutique.colis');
        Route::get('entreprises-{id}-boutiques-{boutique_id}-ramassages-{ramassage_id}', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'showBoutiqueRamassage'])->name('entreprises.boutique.ramassage.show');
        Route::get('entreprises-{id}-boutiques-{boutique_id}-ramassages', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'boutiqueRamassages'])->name('entreprises.boutique.ramassages');
        Route::get('entreprises-{id}-boutiques-{boutique_id}-livraisons-{livraison_id}', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'showBoutiqueLivraison'])->name('entreprises.boutique.livraison.show');
        Route::get('entreprises-{id}-boutiques-{boutique_id}-livraisons', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'boutiqueLivraisons'])->name('entreprises.boutique.livraisons');
        Route::get('entreprises-{id}-boutiques-{boutique_id}', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'showBoutique'])->name('entreprises.boutique.show');
        Route::get('entreprises-{id}-marchands', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'marchands'])->name('entreprises.marchands');
        Route::get('entreprises-{id}-boutiques', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'boutiques'])->name('entreprises.boutiques');
        Route::get('entreprises-{id}-ramassages-{ramassage_id}', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'showRamassageEntreprise'])->name('entreprises.ramassage.show');
        Route::get('entreprises-{id}-ramassages', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'ramassages'])->name('entreprises.ramassages');
        Route::get('entreprises-{id}-livraisons', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'livraisons'])->name('entreprises.livraisons');
        Route::get('entreprises-{id}-colis', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'colis'])->name('entreprises.colis');
        Route::get('entreprises-{id}-livreurs-{livreur_id}', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'showLivreur'])->name('entreprises.livreur.show');
        Route::get('entreprises-{id}-livreurs', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'livreurs'])->name('entreprises.livreurs');
        Route::get('entreprises-{id}-config', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'config'])->name('entreprises.config');
        Route::get('entreprises-{id}-tarifs-livraison', [App\Http\Controllers\PlatformAdmin\EntrepriseController::class, 'tarifsLivraison'])->name('entreprises.tarifs-livraison');

        // Gestion des utilisateurs
        Route::get('users', [App\Http\Controllers\PlatformAdmin\UserController::class, 'index'])->name('users.index');
        Route::get('users/{id}', [App\Http\Controllers\PlatformAdmin\UserController::class, 'show'])->name('users.show');
        Route::delete('users/{id}', [App\Http\Controllers\PlatformAdmin\UserController::class, 'destroy'])->name('users.destroy');

        // Gestion des plans tarifaires
        Route::resource('pricing-plans', App\Http\Controllers\PlatformAdmin\PricingPlanController::class);

        // Gestion des modules pour les pricing plans
        Route::post('pricing-plans/{plan}/modules/{module}/attach', [App\Http\Controllers\PlatformAdmin\PricingPlanController::class, 'attachModule'])->name('pricing-plans.modules.attach');
        Route::post('pricing-plans/{plan}/modules/{module}/detach', [App\Http\Controllers\PlatformAdmin\PricingPlanController::class, 'detachModule'])->name('pricing-plans.modules.detach');
        Route::post('pricing-plans/{plan}/modules/{module}/toggle', [App\Http\Controllers\PlatformAdmin\PricingPlanController::class, 'toggleModule'])->name('pricing-plans.modules.toggle');
        Route::put('pricing-plans/{plan}/modules/{module}/configure', [App\Http\Controllers\PlatformAdmin\PricingPlanController::class, 'configureModule'])->name('pricing-plans.modules.configure');

        // Gestion des abonnements
        // Routes spécifiques en premier pour éviter les conflits
        Route::get('subscriptions/upgrade-history', [App\Http\Controllers\PlatformAdmin\SubscriptionController::class, 'upgradeHistory'])->name('subscriptions.upgrade-history');
        Route::get('subscriptions', [App\Http\Controllers\PlatformAdmin\SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('subscriptions/{id}', [App\Http\Controllers\PlatformAdmin\SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::get('entreprises-{entreprise_id}-upgrade-subscription', [App\Http\Controllers\PlatformAdmin\SubscriptionController::class, 'showUpgradeForm'])->name('subscriptions.upgrade-form');
        Route::post('entreprises/{entreprise_id}/upgrade-subscription', [App\Http\Controllers\PlatformAdmin\SubscriptionController::class, 'upgrade'])->name('subscriptions.upgrade');

        // Données globales (toutes entreprises)
        Route::get('global-data/livraisons', [App\Http\Controllers\PlatformAdmin\GlobalDataController::class, 'livraisons'])->name('global-data.livraisons');
        Route::get('global-data/colis', [App\Http\Controllers\PlatformAdmin\GlobalDataController::class, 'colis'])->name('global-data.colis');
        Route::get('global-data/ramassages', [App\Http\Controllers\PlatformAdmin\GlobalDataController::class, 'ramassages'])->name('global-data.ramassages');
        Route::get('global-data/livreurs', [App\Http\Controllers\PlatformAdmin\GlobalDataController::class, 'livreurs'])->name('global-data.livreurs');
        Route::get('global-data/boutiques', [App\Http\Controllers\PlatformAdmin\GlobalDataController::class, 'boutiques'])->name('global-data.boutiques');

        // Logs système
        Route::get('/logs', [App\Http\Controllers\PlatformAdmin\LogController::class, 'index'])->name('logs.index');
        Route::get('/logs/{id}', [App\Http\Controllers\PlatformAdmin\LogController::class, 'show'])->name('logs.show');

        // Gestion RBAC - Administrateurs
        Route::get('admin-users', [App\Http\Controllers\PlatformAdmin\AdminUserController::class, 'index'])->name('admin-users.index');
        Route::get('admin-users/create', [App\Http\Controllers\PlatformAdmin\AdminUserController::class, 'create'])->name('admin-users.create');
        Route::post('admin-users', [App\Http\Controllers\PlatformAdmin\AdminUserController::class, 'store'])->name('admin-users.store');
        Route::get('admin-users/{id}', [App\Http\Controllers\PlatformAdmin\AdminUserController::class, 'show'])->name('admin-users.show');
        Route::get('admin-users-{id}-edit', [App\Http\Controllers\PlatformAdmin\AdminUserController::class, 'edit'])->name('admin-users.edit');
        Route::put('admin-users/{id}', [App\Http\Controllers\PlatformAdmin\AdminUserController::class, 'update'])->name('admin-users.update');
        Route::delete('admin-users/{id}', [App\Http\Controllers\PlatformAdmin\AdminUserController::class, 'destroy'])->name('admin-users.destroy');
        Route::post('admin-users/{id}/toggle-status', [App\Http\Controllers\PlatformAdmin\AdminUserController::class, 'toggleStatus'])->name('admin-users.toggle-status');

        // Gestion RBAC - Rôles
        Route::get('roles', [App\Http\Controllers\PlatformAdmin\RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [App\Http\Controllers\PlatformAdmin\RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [App\Http\Controllers\PlatformAdmin\RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{id}', [App\Http\Controllers\PlatformAdmin\RoleController::class, 'show'])->name('roles.show');
        Route::get('roles-{id}-edit', [App\Http\Controllers\PlatformAdmin\RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{id}', [App\Http\Controllers\PlatformAdmin\RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{id}', [App\Http\Controllers\PlatformAdmin\RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('roles-{id}-assign-permissions', [App\Http\Controllers\PlatformAdmin\RoleController::class, 'assignPermissions'])->name('roles.assign-permissions');

        // Gestion RBAC - Permissions
        Route::get('permissions', [App\Http\Controllers\PlatformAdmin\PermissionController::class, 'index'])->name('permissions.index');

        // Attribution de rôles et permissions aux admins
        Route::post('admin-users/{id}/assign-roles', [App\Http\Controllers\PlatformAdmin\AdminRoleController::class, 'assignRoles'])->name('admin-users.assign-roles');
        Route::delete('admin-users/{id}-roles-{roleId}', [App\Http\Controllers\PlatformAdmin\AdminRoleController::class, 'removeRole'])->name('admin-users.remove-role');
        Route::post('admin-users/{id}/assign-permissions', [App\Http\Controllers\PlatformAdmin\AdminRoleController::class, 'assignPermissions'])->name('admin-users.assign-permissions');
        Route::delete('admin-users/{id}/permissions/{permissionId}', [App\Http\Controllers\PlatformAdmin\AdminRoleController::class, 'removePermission'])->name('admin-users.remove-permission');

        // Gestion des modules
        Route::get('modules', [App\Http\Controllers\PlatformAdmin\ModuleController::class, 'index'])->name('modules.index');
        Route::get('modules-{module}-edit', [App\Http\Controllers\PlatformAdmin\ModuleController::class, 'edit'])->name('modules.edit');
        Route::put('modules/{module}', [App\Http\Controllers\PlatformAdmin\ModuleController::class, 'update'])->name('modules.update');
        Route::post('pricing-plans-{pricingPlan}-modules/attach', [App\Http\Controllers\PlatformAdmin\ModuleController::class, 'attachToPricingPlan'])->name('modules.attach');
        Route::delete('pricing-plans-{pricingPlan}-modules-{module}/detach', [App\Http\Controllers\PlatformAdmin\ModuleController::class, 'detachFromPricingPlan'])->name('modules.detach');
        Route::put('pricing-plans-{pricingPlan}-modules-{module}/limits', [App\Http\Controllers\PlatformAdmin\ModuleController::class, 'updateLimits'])->name('modules.update-limits');
        Route::post('pricing-plans-{pricingPlan}-modules/update', [App\Http\Controllers\PlatformAdmin\ModuleController::class, 'updateModulesForPricingPlan'])->name('modules.update-for-plan');
    });
});
