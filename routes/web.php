<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


// Route de connexion
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::post('/login', [AuthController::class, 'loginUser'])->name('auth.login.post');

// Routes Super Administrateur Platform
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

        // Gestion des entreprises (uniquement lecture et activation/désactivation)
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

        // Gestion des utilisateurs (uniquement consultation et suppression)
        Route::get('users', [App\Http\Controllers\PlatformAdmin\UserController::class, 'index'])->name('users.index');
        Route::get('users/{id}', [App\Http\Controllers\PlatformAdmin\UserController::class, 'show'])->name('users.show');
        Route::delete('users/{id}', [App\Http\Controllers\PlatformAdmin\UserController::class, 'destroy'])->name('users.destroy');

        // Gestion des plans tarifaires
        Route::resource('pricing-plans', App\Http\Controllers\PlatformAdmin\PricingPlanController::class);

        // Gestion des abonnements (consultation et upgrade)
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
    });
});
