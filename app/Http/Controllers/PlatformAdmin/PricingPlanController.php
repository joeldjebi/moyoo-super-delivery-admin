<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PricingPlanController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('pricing_plans.read')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les plans tarifaires.');
        }
        $data['title'] = 'Plans tarifaires';
        $data['menu'] = 'pricing-plans';
        $query = PricingPlan::query();

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtre par statut actif
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        // Filtre par période
        if ($request->has('period') && $request->period) {
            $query->where('period', $request->period);
        }

        $data['plans'] = $query->with('modules')->orderBy('sort_order', 'asc')
                      ->orderBy('created_at', 'desc')
                      ->paginate(15);

        return view('platform-admin.pricing-plans.index', $data);
    }

    public function create()
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('pricing_plans.create')) {
            abort(403, 'Vous n\'avez pas la permission de créer des plans tarifaires.');
        }
        $data['title'] = 'Créer un plan tarifaire';
        $data['menu'] = 'pricing-plans';
        return view('platform-admin.pricing-plans.create', $data);
    }

    public function store(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('pricing_plans.create')) {
            abort(403, 'Vous n\'avez pas la permission de créer des plans tarifaires.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'period' => 'required|string|in:month,year',
            'features' => 'nullable|string', // JSON string
            'is_popular' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'period' => $validated['period'],
            'features' => $validated['features'] ?? null,
            'is_popular' => $validated['is_popular'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('pricing_plans')->insert($data);

        Log::info('Plan tarifaire créé par super admin', [
            'admin_id' => auth()->guard('platform_admin')->id(),
            'plan_data' => $validated
        ]);

        return redirect()->route('platform-admin.pricing-plans.index')
            ->with('success', 'Plan tarifaire créé avec succès.');
    }

    public function show(string $id)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403, 'Seuls les super administrateurs peuvent accéder à cette ressource.');
        }

        $plan = PricingPlan::with('modules')->findOrFail($id);
        // Récupérer tous les modules (actifs et inactifs) pour la gestion
        $allModules = Module::orderBy('name')->get();

        // Récupérer les modules attachés avec leurs statuts
        $attachedModules = $plan->modules()->get()->keyBy('id');

        // Préparer les données pour chaque module
        $modulesData = $allModules->map(function ($module) use ($attachedModules, $plan) {
            $attached = $attachedModules->has($module->id);
            $pivot = $attached ? $attachedModules->get($module->id)->pivot : null;

            return [
                'module' => $module,
                'attached' => $attached,
                'is_enabled' => $attached && $pivot ? (bool)$pivot->is_enabled : false,
                'limits' => $attached && $pivot && $pivot->limits ? json_decode($pivot->limits, true) : null,
            ];
        });

        $data = [
            'title' => 'Détails du plan tarifaire',
            'menu' => 'pricing-plans',
            'plan' => $plan,
            'modules' => $modulesData,
        ];

        return view('platform-admin.pricing-plans.show', $data);
    }

    public function edit(string $id)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('pricing_plans.update')) {
            abort(403, 'Vous n\'avez pas la permission de modifier les plans tarifaires.');
        }
        $plan = DB::table('pricing_plans')->where('id', $id)->first();

        if (!$plan) {
            abort(404);
        }

        return view('platform-admin.pricing-plans.edit', compact('plan'));
    }

    public function update(Request $request, string $id)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('pricing_plans.update')) {
            abort(403, 'Vous n\'avez pas la permission de modifier les plans tarifaires.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'period' => 'required|string|in:month,year',
            'features' => 'nullable|string', // JSON string
            'is_popular' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'period' => $validated['period'],
            'features' => $validated['features'] ?? null,
            'is_popular' => $validated['is_popular'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
            'updated_at' => now(),
        ];

        DB::table('pricing_plans')->where('id', $id)->update($data);

        Log::info('Plan tarifaire modifié par super admin', [
            'admin_id' => auth()->guard('platform_admin')->id(),
            'plan_id' => $id,
            'plan_data' => $validated
        ]);

        return redirect()->route('platform-admin.pricing-plans.index')
            ->with('success', 'Plan tarifaire modifié avec succès.');
    }

    public function destroy(string $id)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('pricing_plans.delete')) {
            abort(403, 'Vous n\'avez pas la permission de supprimer les plans tarifaires.');
        }
        DB::table('pricing_plans')->where('id', $id)->delete();

        Log::info('Plan tarifaire supprimé par super admin', [
            'admin_id' => auth()->guard('platform_admin')->id(),
            'plan_id' => $id,
        ]);

        return redirect()->route('platform-admin.pricing-plans.index')
            ->with('success', 'Plan tarifaire supprimé avec succès.');
    }

    /**
     * Attacher un module à un pricing plan
     */
    public function attachModule(Request $request, string $planId, string $moduleId)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403, 'Seuls les super administrateurs peuvent accéder à cette ressource.');
        }

        $plan = PricingPlan::findOrFail($planId);
        $module = Module::findOrFail($moduleId);

        // Attacher le module (la méthode gère déjà le cas où il est déjà attaché)
        $plan->attachModule($moduleId, true);

        Log::info('Module attaché au pricing plan', [
            'admin_id' => $admin->id,
            'plan_id' => $planId,
            'module_id' => $moduleId,
        ]);

        return redirect()->route('platform-admin.pricing-plans.show', $planId)
            ->with('success', "Le module \"{$module->name}\" a été attaché au plan avec succès.");
    }

    /**
     * Détacher un module d'un pricing plan
     */
    public function detachModule(string $planId, string $moduleId)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403, 'Seuls les super administrateurs peuvent accéder à cette ressource.');
        }

        $plan = PricingPlan::findOrFail($planId);
        $module = Module::findOrFail($moduleId);

        $plan->detachModule($moduleId);

        Log::info('Module détaché du pricing plan', [
            'admin_id' => $admin->id,
            'plan_id' => $planId,
            'module_id' => $moduleId,
        ]);

        return redirect()->route('platform-admin.pricing-plans.show', $planId)
            ->with('success', "Le module \"{$module->name}\" a été détaché du plan avec succès.");
    }

    /**
     * Activer/désactiver un module pour un pricing plan
     */
    public function toggleModule(string $planId, string $moduleId)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403, 'Seuls les super administrateurs peuvent accéder à cette ressource.');
        }

        $plan = PricingPlan::findOrFail($planId);
        $module = Module::findOrFail($moduleId);

        $isEnabled = $plan->toggleModule($moduleId);

        Log::info('Module activé/désactivé pour le pricing plan', [
            'admin_id' => $admin->id,
            'plan_id' => $planId,
            'module_id' => $moduleId,
            'is_enabled' => $isEnabled,
        ]);

        $status = $isEnabled ? 'activé' : 'désactivé';
        return redirect()->route('platform-admin.pricing-plans.show', $planId)
            ->with('success', "Le module \"{$module->name}\" a été {$status} avec succès.");
    }

    /**
     * Configurer les limites d'un module pour un pricing plan
     */
    public function configureModule(Request $request, string $planId, string $moduleId)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403, 'Seuls les super administrateurs peuvent accéder à cette ressource.');
        }

        $plan = PricingPlan::findOrFail($planId);
        $module = Module::findOrFail($moduleId);

        // Vérifier que le module est attaché
        if (!$plan->modules()->where('modules.id', $moduleId)->exists()) {
            return redirect()->route('platform-admin.pricing-plans.show', $planId)
                ->with('error', 'Ce module n\'est pas attaché à ce plan.');
        }

        // Validation selon le type de module
        $validated = [];

        if ($module->slug === 'stock_management') {
            $validated = $request->validate([
                'max_products' => 'nullable|integer|min:0',
                'max_categories' => 'nullable|integer|min:0',
            ]);
        } else {
            // Pour d'autres modules, accepter n'importe quelle clé/valeur
            $validated = $request->except(['_token', '_method']);
        }

        // Convertir les valeurs vides en null
        $limits = array_map(function ($value) {
            return $value === '' || $value === null ? null : (int)$value;
        }, $validated);

        // Filtrer les valeurs null
        $limits = array_filter($limits, function ($value) {
            return $value !== null;
        });

        $plan->configureModule($moduleId, $limits);

        Log::info('Limites du module configurées pour le pricing plan', [
            'admin_id' => $admin->id,
            'plan_id' => $planId,
            'module_id' => $moduleId,
            'limits' => $limits,
        ]);

        return redirect()->route('platform-admin.pricing-plans.show', $planId)
            ->with('success', "Les limites du module \"{$module->name}\" ont été configurées avec succès.");
    }
}
