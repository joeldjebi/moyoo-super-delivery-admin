<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Module;
use App\Models\PricingPlan;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            // Modules Core
            [
                'name' => 'Tableau de bord',
                'slug' => 'dashboard',
                'description' => 'Tableau de bord principal avec statistiques et aperçu général',
                'icon' => 'ti-smart-home',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 1,
                'routes' => ['dashboard'],
            ],
            [
                'name' => 'Gestion des Colis',
                'slug' => 'colis_management',
                'description' => 'Gestion complète des colis : création, suivi, packages',
                'icon' => 'ti-shopping-cart',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 2,
                'routes' => ['colis.index', 'colis.create', 'colis.packages'],
            ],
            [
                'name' => 'Gestion des Ramassages',
                'slug' => 'ramassage_management',
                'description' => 'Gestion des ramassages et planification',
                'icon' => 'ti-shopping-cart',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 3,
                'routes' => ['ramassages.index', 'ramassages.create', 'ramassages.show'],
            ],
            [
                'name' => 'Gestion des Marchands & Boutiques',
                'slug' => 'marchand_management',
                'description' => 'Gestion des marchands et de leurs boutiques',
                'icon' => 'ti-layout-sidebar',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 4,
                'routes' => ['marchands.index', 'marchands.create', 'boutiques.index', 'boutiques.create'],
            ],
            [
                'name' => 'Gestion des Livreurs',
                'slug' => 'livreur_management',
                'description' => 'Gestion des livreurs et de leurs affectations',
                'icon' => 'ti-mail',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 5,
                'routes' => ['livreurs.index', 'livreurs.create', 'livreurs.show'],
            ],
            [
                'name' => 'Rapports de Base',
                'slug' => 'reports_basic',
                'description' => 'Rapports de base : livraisons, colis, ramassages, frais',
                'icon' => 'ti-chart-bar',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 6,
                'routes' => ['rapports.index', 'rapports.show'],
            ],
            [
                'name' => 'Gestion des Reversements',
                'slug' => 'reversement_management',
                'description' => 'Gestion des reversements et balances',
                'icon' => 'ti-wallet',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 7,
                'routes' => ['reversements.index', 'reversements.create', 'balances.index', 'historique.balances'],
            ],
            [
                'name' => 'Gestion des Tarifs de Livraison',
                'slug' => 'tarif_management',
                'description' => 'Gestion des tarifs de livraison',
                'icon' => 'ti-currency-dollar',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 8,
                'routes' => ['tarifs.index', 'tarifs.create'],
            ],
            [
                'name' => 'Gestion des Engins',
                'slug' => 'engin_management',
                'description' => 'Gestion des engins et types d\'engins',
                'icon' => 'ti-messages',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 9,
                'routes' => ['engins.index', 'engins.create', 'type-engins.index', 'type-engins.create'],
            ],
            [
                'name' => 'Gestion des Délais',
                'slug' => 'delai_management',
                'description' => 'Gestion des délais de livraison',
                'icon' => 'ti-calendar',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 10,
                'routes' => ['delais.index', 'delais.create'],
            ],
            [
                'name' => 'Gestion des Utilisateurs',
                'slug' => 'user_management',
                'description' => 'Gestion des utilisateurs de l\'entreprise',
                'icon' => 'ti-users',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 11,
                'routes' => ['users.index', 'users.create', 'users.show'],
            ],
            [
                'name' => 'Gestion des Abonnements',
                'slug' => 'subscription_management',
                'description' => 'Gestion des abonnements de l\'entreprise',
                'icon' => 'ti-crown',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 12,
                'routes' => ['subscriptions.index'],
            ],
            [
                'name' => 'Gestion des Permissions des Rôles',
                'slug' => 'role_permissions',
                'description' => 'Gestion des permissions des rôles utilisateurs',
                'icon' => 'ti-shield',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 13,
                'routes' => ['role-permissions.index', 'role-permissions.update'],
            ],
            [
                'name' => 'Configuration',
                'slug' => 'configuration_management',
                'description' => 'Configuration : modes de livraison, poids, types de colis',
                'icon' => 'ti-layout-kanban',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 14,
                'routes' => ['mode-livraisons.index', 'poids.index', 'type-colis.index'],
            ],
            [
                'name' => 'Notifications',
                'slug' => 'notifications',
                'description' => 'Gestion des notifications',
                'icon' => 'ti-bell',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 15,
                'routes' => ['notifications.settings'],
            ],
            [
                'name' => 'Support',
                'slug' => 'support',
                'description' => 'Support client et assistance',
                'icon' => 'ti-lifebuoy',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 16,
                'routes' => ['support.index'],
            ],
            [
                'name' => 'Documentation',
                'slug' => 'documentation',
                'description' => 'Documentation de l\'application',
                'icon' => 'ti-file-description',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 17,
                'routes' => ['documentation.index'],
            ],
            [
                'name' => 'Paramètres',
                'slug' => 'settings',
                'description' => 'Paramètres : profil, entreprise',
                'icon' => 'ti-settings',
                'category' => 'core',
                'is_active' => true,
                'sort_order' => 18,
                'routes' => ['auth.profile', 'entreprise.index'],
            ],
            // Modules Premium
            [
                'name' => 'Géolocalisation et Suivi',
                'slug' => 'geolocation_tracking',
                'description' => 'Suivi en temps réel des livreurs et colis',
                'icon' => 'ti-map',
                'category' => 'premium',
                'is_active' => true,
                'sort_order' => 19,
                'routes' => ['location.admin-monitor'],
            ],
            [
                'name' => 'Rapports Avancés',
                'slug' => 'reports_advanced',
                'description' => 'Rapports détaillés et analyses avancées',
                'icon' => 'ti-chart-bar',
                'category' => 'premium',
                'is_active' => true,
                'sort_order' => 20,
                'routes' => ['rapports.advanced'],
            ],
            [
                'name' => 'Accès API',
                'slug' => 'api_access',
                'description' => 'Accès à l\'API REST et documentation Swagger',
                'icon' => 'ti-code',
                'category' => 'premium',
                'is_active' => true,
                'sort_order' => 21,
                'routes' => ['api.documentation'],
            ],
            // Module Stock (optionnel)
            [
                'name' => 'Gestion de Stock',
                'slug' => 'stock_management',
                'description' => 'Gestion complète du stock : produits, catégories, inventaire',
                'icon' => 'ti-warehouse',
                'category' => 'premium',
                'is_active' => true,
                'is_optional' => true,
                'price' => null,
                'currency' => 'XOF',
                'sort_order' => 22,
                'routes' => ['stock.index', 'stock.products', 'stock.categories'],
            ],
        ];

        // Créer ou mettre à jour les modules
        foreach ($modules as $moduleData) {
            Module::updateOrCreate(
                ['slug' => $moduleData['slug']],
                $moduleData
            );
        }

        $this->command->info('Modules créés avec succès !');

        // Configurer les modules par défaut pour les pricing plans
        $this->configureDefaultPricingPlans();
    }

    /**
     * Configurer les modules par défaut pour les pricing plans
     */
    protected function configureDefaultPricingPlans(): void
    {
        // Plan Free (id: 1) - Tous les modules core activés avec limites
        $freePlan = PricingPlan::find(1);
        if ($freePlan) {
            $coreModules = Module::where('category', 'core')->get();
            foreach ($coreModules as $module) {
                $limits = null;

                // Définir les limites selon le module
                if ($module->slug === 'colis_management') {
                    $limits = ['max_per_month' => 20];
                } elseif ($module->slug === 'livreur_management') {
                    $limits = ['max_livreurs' => 2];
                } elseif ($module->slug === 'marchand_management') {
                    $limits = ['max_marchands' => 5, 'max_boutiques' => 10];
                } elseif ($module->slug === 'user_management') {
                    $limits = ['max_users' => 3];
                }

                DB::table('pricing_plan_modules')->updateOrInsert(
                    [
                        'pricing_plan_id' => $freePlan->id,
                        'module_id' => $module->id,
                    ],
                    [
                        'is_enabled' => true,
                        'limits' => $limits ? json_encode($limits) : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            $this->command->info('Modules configurés pour le plan Free (id: 1)');
        }

        // Plan Premium (id: 2) - Tous les modules core + premium activés, limites illimitées
        $premiumPlan = PricingPlan::find(2);
        if ($premiumPlan) {
            $allModules = Module::where('is_active', true)->get();
            foreach ($allModules as $module) {
                DB::table('pricing_plan_modules')->updateOrInsert(
                    [
                        'pricing_plan_id' => $premiumPlan->id,
                        'module_id' => $module->id,
                    ],
                    [
                        'is_enabled' => true,
                        'limits' => null, // Illimité
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            $this->command->info('Modules configurés pour le plan Premium (id: 2)');
        }

        // Plan Premium Annuel (id: 3) - Identique au Premium
        $premiumAnnualPlan = PricingPlan::find(3);
        if ($premiumAnnualPlan) {
            $allModules = Module::where('is_active', true)->get();
            foreach ($allModules as $module) {
                DB::table('pricing_plan_modules')->updateOrInsert(
                    [
                        'pricing_plan_id' => $premiumAnnualPlan->id,
                        'module_id' => $module->id,
                    ],
                    [
                        'is_enabled' => true,
                        'limits' => null, // Illimité
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            $this->command->info('Modules configurés pour le plan Premium Annuel (id: 3)');
        }
    }
}
