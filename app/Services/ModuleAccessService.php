<?php

namespace App\Services;

use App\Models\Entreprise;
use App\Models\Module;
use App\Models\PricingPlan;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

class ModuleAccessService
{
    /**
     * Vérifier si l'entreprise a accès à un module
     */
    public function hasAccess(int $entrepriseId, string $moduleSlug): bool
    {
        // Le module dashboard est toujours accessible
        if ($moduleSlug === 'dashboard') {
            return true;
        }

        // Récupérer l'abonnement actif de l'entreprise
        $subscription = SubscriptionPlan::where('entreprise_id', $entrepriseId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$subscription || !$subscription->pricingPlan) {
            return false;
        }

        // Vérifier si le pricing plan a le module activé
        return $subscription->pricingPlan->hasModule($moduleSlug);
    }

    /**
     * Obtenir une limite spécifique d'un module pour une entreprise
     */
    public function getModuleLimit(int $entrepriseId, string $moduleSlug, string $limitKey): ?int
    {
        // Récupérer l'abonnement actif de l'entreprise
        $subscription = SubscriptionPlan::where('entreprise_id', $entrepriseId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('pricingPlan')
            ->first();

        if (!$subscription || !$subscription->pricingPlan) {
            return null;
        }

        // Obtenir les limites du module
        $limits = $subscription->pricingPlan->getModuleLimits($moduleSlug);

        if (!$limits || !isset($limits[$limitKey])) {
            return null; // Pas de limite (illimité)
        }

        return (int) $limits[$limitKey];
    }

    /**
     * Vérifier si l'entreprise peut créer des colis (avec limite)
     */
    public function canCreateColis(int $entrepriseId): array
    {
        if (!$this->hasAccess($entrepriseId, 'colis_management')) {
            return [
                'allowed' => false,
                'message' => 'Le module de gestion des colis n\'est pas disponible dans votre plan.',
            ];
        }

        // Vérifier la limite mensuelle
        $maxPerMonth = $this->getModuleLimit($entrepriseId, 'colis_management', 'max_per_month');

        if ($maxPerMonth !== null) {
            $currentMonthCount = DB::table('colis')
                ->where('entreprise_id', $entrepriseId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            if ($currentMonthCount >= $maxPerMonth) {
                return [
                    'allowed' => false,
                    'message' => "Vous avez atteint la limite mensuelle de {$maxPerMonth} colis pour votre plan.",
                    'current' => $currentMonthCount,
                    'limit' => $maxPerMonth,
                ];
            }
        }

        return [
            'allowed' => true,
            'current' => $maxPerMonth !== null ? DB::table('colis')
                ->where('entreprise_id', $entrepriseId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count() : null,
            'limit' => $maxPerMonth,
        ];
    }

    /**
     * Vérifier si l'entreprise peut créer des livreurs (avec limite)
     */
    public function canCreateLivreur(int $entrepriseId): array
    {
        if (!$this->hasAccess($entrepriseId, 'livreur_management')) {
            return [
                'allowed' => false,
                'message' => 'Le module de gestion des livreurs n\'est pas disponible dans votre plan.',
            ];
        }

        // Vérifier la limite de livreurs
        $maxLivreurs = $this->getModuleLimit($entrepriseId, 'livreur_management', 'max_livreurs');

        if ($maxLivreurs !== null) {
            $currentCount = DB::table('livreurs')
                ->where('entreprise_id', $entrepriseId)
                ->whereNull('deleted_at')
                ->count();

            if ($currentCount >= $maxLivreurs) {
                return [
                    'allowed' => false,
                    'message' => "Vous avez atteint la limite de {$maxLivreurs} livreurs pour votre plan.",
                    'current' => $currentCount,
                    'limit' => $maxLivreurs,
                ];
            }
        }

        return [
            'allowed' => true,
            'current' => $maxLivreurs !== null ? DB::table('livreurs')
                ->where('entreprise_id', $entrepriseId)
                ->whereNull('deleted_at')
                ->count() : null,
            'limit' => $maxLivreurs,
        ];
    }

    /**
     * Vérifier si l'entreprise peut créer des marchands (avec limite)
     */
    public function canCreateMarchand(int $entrepriseId): array
    {
        if (!$this->hasAccess($entrepriseId, 'marchand_management')) {
            return [
                'allowed' => false,
                'message' => 'Le module de gestion des marchands n\'est pas disponible dans votre plan.',
            ];
        }

        // Vérifier la limite de marchands
        $maxMarchands = $this->getModuleLimit($entrepriseId, 'marchand_management', 'max_marchands');

        if ($maxMarchands !== null) {
            $currentCount = DB::table('marchands')
                ->where('entreprise_id', $entrepriseId)
                ->whereNull('deleted_at')
                ->count();

            if ($currentCount >= $maxMarchands) {
                return [
                    'allowed' => false,
                    'message' => "Vous avez atteint la limite de {$maxMarchands} marchands pour votre plan.",
                    'current' => $currentCount,
                    'limit' => $maxMarchands,
                ];
            }
        }

        return [
            'allowed' => true,
            'current' => $maxMarchands !== null ? DB::table('marchands')
                ->where('entreprise_id', $entrepriseId)
                ->whereNull('deleted_at')
                ->count() : null,
            'limit' => $maxMarchands,
        ];
    }

    /**
     * Vérifier si l'entreprise peut créer des utilisateurs (avec limite)
     */
    public function canCreateUser(int $entrepriseId): array
    {
        if (!$this->hasAccess($entrepriseId, 'user_management')) {
            return [
                'allowed' => false,
                'message' => 'Le module de gestion des utilisateurs n\'est pas disponible dans votre plan.',
            ];
        }

        // Vérifier la limite d'utilisateurs
        $maxUsers = $this->getModuleLimit($entrepriseId, 'user_management', 'max_users');

        if ($maxUsers !== null) {
            $currentCount = DB::table('users')
                ->where('entreprise_id', $entrepriseId)
                ->whereNull('deleted_at')
                ->count();

            if ($currentCount >= $maxUsers) {
                return [
                    'allowed' => false,
                    'message' => "Vous avez atteint la limite de {$maxUsers} utilisateurs pour votre plan.",
                    'current' => $currentCount,
                    'limit' => $maxUsers,
                ];
            }
        }

        return [
            'allowed' => true,
            'current' => $maxUsers !== null ? DB::table('users')
                ->where('entreprise_id', $entrepriseId)
                ->whereNull('deleted_at')
                ->count() : null,
            'limit' => $maxUsers,
        ];
    }

    /**
     * Obtenir tous les modules accessibles pour une entreprise
     */
    public function getAccessibleModules(int $entrepriseId): array
    {
        // Récupérer l'abonnement actif de l'entreprise
        $subscription = SubscriptionPlan::where('entreprise_id', $entrepriseId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('pricingPlan.modules')
            ->first();

        if (!$subscription || !$subscription->pricingPlan) {
            // Retourner uniquement le module dashboard
            return ['dashboard'];
        }

        // Récupérer tous les modules activés pour ce pricing plan
        $modules = $subscription->pricingPlan->modules()
            ->where('pricing_plan_modules.is_enabled', true)
            ->where('modules.is_active', true)
            ->pluck('slug')
            ->toArray();

        // Toujours inclure le dashboard
        if (!in_array('dashboard', $modules)) {
            $modules[] = 'dashboard';
        }

        return $modules;
    }

    /**
     * Obtenir le pricing plan actif d'une entreprise
     */
    public function getActivePricingPlan(int $entrepriseId): ?PricingPlan
    {
        $subscription = SubscriptionPlan::where('entreprise_id', $entrepriseId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('pricingPlan')
            ->first();

        return $subscription?->pricingPlan;
    }
}

