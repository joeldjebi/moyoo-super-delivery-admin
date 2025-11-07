<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Entreprise;
use App\Models\PricingPlan;
use App\Models\SubscriptionUpgradeHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('subscriptions.read')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les abonnements.');
        }
        $data['title'] = 'Abonnements';
        $data['menu'] = 'subscriptions';
        $query = SubscriptionPlan::with(['entreprise', 'pricingPlan']);

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('entreprise', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('pricingPlan', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par entreprise
        if ($request->has('entreprise_id') && $request->entreprise_id) {
            $query->where('entreprise_id', $request->entreprise_id);
        }

        // Filtre par statut actif (boolean en PostgreSQL)
        if ($request->has('is_active') && $request->is_active !== '') {
            // Convertir '1'/'0' string en boolean pour PostgreSQL
            $isActive = in_array($request->is_active, ['1', 'true', true, 1], true);
            $query->where('is_active', $isActive);
        }

        // Filtre par statut d'expiration
        if ($request->has('expired') && $request->expired) {
            if ($request->expired == 'yes') {
                $query->expired();
            } elseif ($request->expired == 'no') {
                $query->notExpired();
            }
        }

        // Tri : si created_at est NULL, utiliser id comme fallback
        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['subscriptions'] = $query->orderByRaw('created_at DESC NULLS LAST')
                               ->orderBy('id', 'desc')
                               ->paginate($perPage)
                               ->appends(request()->query());

        // Récupérer la liste des entreprises pour le filtre
        $data['entreprises'] = Entreprise::active()
            ->orderBy('name')
            ->get();

        // Calculer les statistiques sur les abonnements
        $data['stats'] = [
            'total_abonnements' => DB::table('subscription_plans')->count(),
            'abonnements_actifs' => DB::table('subscription_plans')->where('is_active', true)->count(),
            'abonnements_inactifs' => DB::table('subscription_plans')->where('is_active', false)->count(),
            'abonnements_expires' => DB::table('subscription_plans')
                ->where('is_active', true)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count(),
            'abonnements_non_expires' => DB::table('subscription_plans')
                ->where('is_active', true)
                ->where(function($q) {
                    $q->where('expires_at', '>=', now())
                      ->orWhereNull('expires_at');
                })
                ->count(),
            'revenus_totaux' => DB::table('subscription_plans')
                ->where('is_active', true)
                ->sum('price') ?? 0,
            'entreprises_abonnees' => DB::table('subscription_plans')
                ->where('is_active', true)
                ->distinct()
                ->count('entreprise_id'),
        ];

        return view('platform-admin.subscriptions.index', $data);
    }

    // Note: La création et modification d'abonnements par le super admin est désactivée

    public function show(string $id)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('subscriptions.read')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les abonnements.');
        }
        $subscription = SubscriptionPlan::with(['entreprise', 'pricingPlan'])
            ->findOrFail($id);

        return view('platform-admin.subscriptions.show', compact('subscription'));
    }

    /**
     * Afficher le formulaire d'upgrade d'abonnement pour une entreprise
     */
    public function showUpgradeForm(string $entreprise_id)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('subscriptions.upgrade_form')) {
            abort(403, 'Vous n\'avez pas la permission d\'accéder au formulaire d\'upgrade.');
        }
        $data['title'] = 'Upgrade d\'abonnement';
        $data['menu'] = 'subscriptions';
        $data['entreprise'] = Entreprise::whereNull('deleted_at')->findOrFail($entreprise_id);

        // Récupérer l'abonnement actuel de l'entreprise
        $data['current_subscription'] = SubscriptionPlan::where('entreprise_id', $entreprise_id)
            ->where('is_active', true)
            ->with(['pricingPlan'])
            ->first();

        // Récupérer tous les plans tarifaires actifs disponibles
        $data['pricing_plans'] = PricingPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('platform-admin.subscriptions.upgrade', $data);
    }

    /**
     * Traiter l'upgrade d'abonnement
     */
    public function upgrade(Request $request, string $entreprise_id)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('subscriptions.upgrade')) {
            abort(403, 'Vous n\'avez pas la permission d\'upgrader un abonnement.');
        }
        $validated = $request->validate([
            'nouveau_pricing_plan_id' => 'required|exists:pricing_plans,id',
            'raison' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'started_at' => 'nullable|date',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        $entreprise = Entreprise::whereNull('deleted_at')->findOrFail($entreprise_id);
        $nouveauPricingPlan = PricingPlan::findOrFail($validated['nouveau_pricing_plan_id']);

        // Récupérer l'abonnement actuel
        $ancienSubscription = SubscriptionPlan::where('entreprise_id', $entreprise_id)
            ->where('is_active', true)
            ->first();

        // Désactiver l'ancien abonnement s'il existe
        if ($ancienSubscription) {
            $ancienSubscription->is_active = false;
            $ancienSubscription->save();
        }

        // Créer le nouveau abonnement
        $nouveauSubscription = new SubscriptionPlan();
        $nouveauSubscription->entreprise_id = $entreprise_id;
        $nouveauSubscription->pricing_plan_id = $nouveauPricingPlan->id;
        $nouveauSubscription->name = $nouveauPricingPlan->name . ' - ' . $entreprise->name;
        $nouveauSubscription->slug = Str::slug($nouveauPricingPlan->name . '-' . $entreprise->name . '-' . now()->format('Y-m-d'));
        $nouveauSubscription->description = $nouveauPricingPlan->description;
        $nouveauSubscription->price = $nouveauPricingPlan->price;
        $nouveauSubscription->currency = $nouveauPricingPlan->currency ?? 'FCFA';
        $durationDays = $nouveauPricingPlan->period == 'year' ? 365 : 30;
        $nouveauSubscription->duration_days = $durationDays;

        // Gérer les features (peut être un array ou un JSON string)
        $features = is_array($nouveauPricingPlan->features) ? $nouveauPricingPlan->features : (is_string($nouveauPricingPlan->features) ? json_decode($nouveauPricingPlan->features, true) : []);
        $nouveauSubscription->features = $features;
        $nouveauSubscription->max_colis_per_month = $features['max_colis_per_month'] ?? null;
        $nouveauSubscription->max_livreurs = $features['max_livreurs'] ?? null;
        $nouveauSubscription->max_marchands = $features['max_marchands'] ?? null;
        $nouveauSubscription->whatsapp_notifications = $features['whatsapp_notifications'] ?? false;
        $nouveauSubscription->firebase_notifications = $features['firebase_notifications'] ?? false;
        $nouveauSubscription->api_access = $features['api_access'] ?? false;
        $nouveauSubscription->advanced_reports = $features['advanced_reports'] ?? false;
        $nouveauSubscription->priority_support = $features['priority_support'] ?? false;
        $nouveauSubscription->is_active = true;

        // Calculer les dates
        $startedAt = $validated['started_at'] ? \Carbon\Carbon::parse($validated['started_at']) : now();
        $nouveauSubscription->started_at = $startedAt;
        // Calculer automatiquement la date d'expiration en fonction du plan tarifaire
        $nouveauSubscription->expires_at = $startedAt->copy()->addDays($durationDays);
        $nouveauSubscription->save();

        // Gérer l'upload du document si présent
        $documentPath = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $fileName = 'upgrade_' . $entreprise_id . '_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $documentPath = $file->storeAs('subscription-upgrades/documents', $fileName, 'public');
        }

        // Enregistrer l'historique de l'upgrade
        $upgradeHistory = new SubscriptionUpgradeHistory();
        $upgradeHistory->entreprise_id = $entreprise_id;
        $upgradeHistory->ancien_subscription_plan_id = $ancienSubscription ? $ancienSubscription->id : null;
        $upgradeHistory->nouveau_subscription_plan_id = $nouveauSubscription->id;
        $upgradeHistory->ancien_pricing_plan_id = $ancienSubscription && $ancienSubscription->pricingPlan ? $ancienSubscription->pricing_plan_id : null;
        $upgradeHistory->nouveau_pricing_plan_id = $nouveauPricingPlan->id;
        $upgradeHistory->upgraded_by = auth()->guard('platform_admin')->id();
        $upgradeHistory->ancien_prix = $ancienSubscription ? $ancienSubscription->price : null;
        $upgradeHistory->nouveau_prix = $nouveauPricingPlan->price;
        $upgradeHistory->ancien_currency = $ancienSubscription ? $ancienSubscription->currency : null;
        $upgradeHistory->nouveau_currency = $nouveauPricingPlan->currency ?? 'FCFA';
        $upgradeHistory->raison = $validated['raison'] ?? null;
        $upgradeHistory->notes = $validated['notes'] ?? null;
        $upgradeHistory->document = $documentPath;
        $upgradeHistory->date_upgrade = now();
        $upgradeHistory->save();

        Log::info('Abonnement upgradé par super admin', [
            'admin_id' => auth()->guard('platform_admin')->id(),
            'entreprise_id' => $entreprise_id,
            'ancien_subscription_id' => $ancienSubscription ? $ancienSubscription->id : null,
            'nouveau_subscription_id' => $nouveauSubscription->id,
            'ancien_pricing_plan_id' => $ancienSubscription && $ancienSubscription->pricingPlan ? $ancienSubscription->pricing_plan_id : null,
            'nouveau_pricing_plan_id' => $nouveauPricingPlan->id,
        ]);

        return redirect()->route('platform-admin.subscriptions.index')
            ->with('success', 'Abonnement upgradé avec succès.');
    }

    /**
     * Afficher l'historique des upgrades d'abonnement
     */
    public function upgradeHistory(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('subscriptions.upgrade_history')) {
            abort(403, 'Vous n\'avez pas la permission de consulter l\'historique des upgrades.');
        }
        $data['title'] = 'Historique des upgrades d\'abonnement';
        $data['menu'] = 'subscriptions-upgrade-history';

        // Récupérer l'historique des upgrades
        $query = SubscriptionUpgradeHistory::with([
            'entreprise',
            'ancienSubscriptionPlan',
            'nouveauSubscriptionPlan',
            'ancienPricingPlan',
            'nouveauPricingPlan',
            'upgradedBy'
        ])
        ->whereNull('deleted_at');

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('entreprise', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('ancienPricingPlan', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('nouveauPricingPlan', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhere('raison', 'like', "%{$search}%");
            });
        }

        // Filtre par entreprise
        if ($request->has('entreprise_id') && $request->entreprise_id) {
            $query->where('entreprise_id', $request->entreprise_id);
        }

        // Pagination avec 10 éléments par défaut
        $perPage = $request->get('per_page', 10);
        $data['upgrade_history'] = $query->orderBy('date_upgrade', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        // Récupérer la liste des entreprises pour le filtre
        $data['entreprises'] = Entreprise::whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('platform-admin.subscriptions.upgrade-history', $data);
    }

}
