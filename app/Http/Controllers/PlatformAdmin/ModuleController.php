<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModuleController extends Controller
{
    /**
     * Afficher la liste des modules avec leur statut par pricing_plan
     */
    public function index(Request $request)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('pricing_plans.read')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les modules.');
        }

        $pricingPlans = PricingPlan::active()
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Récupérer tous les modules (actifs et inactifs) pour la gestion
        $modules = Module::orderBy('category', 'asc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // Charger les relations pour chaque pricing plan (forcer le rechargement)
        foreach ($pricingPlans as $plan) {
            $plan->load('modules');
            // Forcer le rechargement pour éviter le cache
            $plan->setRelation('modules', $plan->modules()->withPivot('is_enabled', 'limits')->get());
        }

        return view('platform-admin.modules.index', [
            'menu' => 'modules',
            'pricingPlans' => $pricingPlans,
            'modules' => $modules,
        ]);
    }

    /**
     * Attacher un module à un pricing_plan avec limites
     */
    public function attachToPricingPlan(Request $request, PricingPlan $pricingPlan)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('pricing_plans.update')) {
            abort(403, 'Vous n\'avez pas la permission de modifier les plans tarifaires.');
        }

        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'is_enabled' => 'required|boolean',
            'limits' => 'nullable|array',
        ]);

        $module = Module::findOrFail($validated['module_id']);

        // Vérifier si la relation existe déjà
        $existing = DB::table('pricing_plan_modules')
            ->where('pricing_plan_id', $pricingPlan->id)
            ->where('module_id', $module->id)
            ->first();

        if ($existing) {
            // Mettre à jour la relation existante
            DB::table('pricing_plan_modules')
                ->where('pricing_plan_id', $pricingPlan->id)
                ->where('module_id', $module->id)
                ->update([
                    'is_enabled' => $validated['is_enabled'],
                    'limits' => !empty($validated['limits']) ? json_encode($validated['limits']) : null,
                    'updated_at' => now(),
                ]);
        } else {
            // Créer une nouvelle relation
            DB::table('pricing_plan_modules')->insert([
                'pricing_plan_id' => $pricingPlan->id,
                'module_id' => $module->id,
                'is_enabled' => $validated['is_enabled'],
                'limits' => !empty($validated['limits']) ? json_encode($validated['limits']) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('platform-admin.modules.index')
            ->with('success', "Le module {$module->name} a été " . ($validated['is_enabled'] ? 'activé' : 'désactivé') . " pour le plan {$pricingPlan->name}.");
    }

    /**
     * Détacher un module d'un pricing_plan
     */
    public function detachFromPricingPlan(PricingPlan $pricingPlan, Module $module)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('pricing_plans.update')) {
            abort(403, 'Vous n\'avez pas la permission de modifier les plans tarifaires.');
        }

        DB::table('pricing_plan_modules')
            ->where('pricing_plan_id', $pricingPlan->id)
            ->where('module_id', $module->id)
            ->delete();

        return redirect()
            ->route('platform-admin.modules.index')
            ->with('success', "Le module {$module->name} a été retiré du plan {$pricingPlan->name}.");
    }

    /**
     * Mettre à jour les limites d'un module pour un pricing_plan
     */
    public function updateLimits(Request $request, PricingPlan $pricingPlan, Module $module)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('pricing_plans.update')) {
            abort(403, 'Vous n\'avez pas la permission de modifier les plans tarifaires.');
        }

        $validated = $request->validate([
            'limits' => 'nullable|array',
        ]);

        // Vérifier si la relation existe
        $existing = DB::table('pricing_plan_modules')
            ->where('pricing_plan_id', $pricingPlan->id)
            ->where('module_id', $module->id)
            ->first();

        if (!$existing) {
            return back()
                ->with('error', 'Le module n\'est pas associé à ce plan tarifaire.');
        }

        // Mettre à jour les limites
        DB::table('pricing_plan_modules')
            ->where('pricing_plan_id', $pricingPlan->id)
            ->where('module_id', $module->id)
            ->update([
                'limits' => !empty($validated['limits']) ? json_encode($validated['limits']) : null,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('platform-admin.modules.index')
            ->with('success', "Les limites du module {$module->name} ont été mises à jour pour le plan {$pricingPlan->name}.");
    }

    /**
     * Mettre à jour en masse les modules pour un pricing_plan
     */
    public function updateModulesForPricingPlan(Request $request, PricingPlan $pricingPlan)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->hasPermission('pricing_plans.update')) {
            abort(403, 'Vous n\'avez pas la permission de modifier les plans tarifaires.');
        }

        // Récupérer tous les modules (actifs et inactifs) pour traiter même ceux non cochés
        $allModules = Module::get();
        $submittedModules = $request->input('modules', []);

        DB::beginTransaction();
        try {
            // Préparer les données pour la synchronisation
            $syncData = [];

            foreach ($allModules as $module) {
                $moduleData = $submittedModules[$module->id] ?? null;

                // Si le module est dans les données soumises, il est activé
                $isEnabled = $moduleData !== null;

                // Récupérer les limites si le module est activé
                $limits = null;
                if ($isEnabled && isset($moduleData['limits']) && is_array($moduleData['limits'])) {
                    // Filtrer les valeurs vides et convertir en entier
                    $filteredLimits = [];
                    foreach ($moduleData['limits'] as $key => $value) {
                        if ($value !== null && $value !== '' && $value !== '0') {
                            $filteredLimits[$key] = (int) $value;
                        }
                    }
                    $limits = !empty($filteredLimits) ? $filteredLimits : null;
                }

                // Préparer les données pour sync
                // Convertir is_enabled en boolean explicite
                $syncData[$module->id] = [
                    'is_enabled' => (bool) $isEnabled,
                    'limits' => $limits ? json_encode($limits) : null,
                ];
            }

            // Utiliser sync pour mettre à jour toutes les relations en une seule fois
            // sync() remplace toutes les relations existantes par celles fournies
            $pricingPlan->modules()->sync($syncData);

            DB::commit();

            // Forcer le rechargement du modèle et des relations pour éviter le cache
            $pricingPlan->refresh();
            $pricingPlan->unsetRelation('modules');
            $pricingPlan->load('modules');

            Log::info('Modules mis à jour pour le plan tarifaire', [
                'pricing_plan_id' => $pricingPlan->id,
                'pricing_plan_name' => $pricingPlan->name,
                'modules_count' => count($syncData),
            ]);

            return redirect()
                ->route('platform-admin.modules.index')
                ->with('success', "Les modules ont été mis à jour pour le plan {$pricingPlan->name}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour des modules', [
                'pricing_plan_id' => $pricingPlan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()
                ->withInput()
                ->with('error', "Erreur lors de la mise à jour : {$e->getMessage()}");
        }
    }

    /**
     * Afficher le formulaire d'édition d'un module
     */
    public function edit(Module $module)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403, 'Seuls les super administrateurs peuvent modifier les modules.');
        }

        return view('platform-admin.modules.edit', [
            'menu' => 'modules',
            'module' => $module,
        ]);
    }

    /**
     * Mettre à jour un module (notamment son prix)
     */
    public function update(Request $request, Module $module)
    {
        $admin = Auth::guard('platform_admin')->user();
        if (!$admin || !$admin->isSuperAdmin()) {
            abort(403, 'Seuls les super administrateurs peuvent modifier les modules.');
        }

        // Règles de validation conditionnelles
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'category' => 'required|string|in:core,advanced,premium',
            'is_active' => 'nullable',
            'is_optional' => 'nullable',
            'sort_order' => 'nullable|integer|min:0',
        ];

        // Seul le module stock_management peut être optionnel
        if ($module->slug === 'stock_management') {
            $rules['price'] = 'nullable|numeric|min:0';
            $rules['currency'] = 'nullable|string|max:3';
        } else {
            $rules['price'] = 'nullable';
            $rules['currency'] = 'nullable|string|max:3';
        }

        $validated = $request->validate($rules);

        // Convertir les valeurs des checkboxes en boolean
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : false;

        // Seul le module stock_management peut être optionnel
        if ($module->slug === 'stock_management') {
            $validated['is_optional'] = $request->has('is_optional') ? (bool)$request->input('is_optional') : false;

            // Si le module n'est pas optionnel, supprimer le prix
            if (!$validated['is_optional']) {
                // Retirer price et currency du tableau pour éviter les erreurs NOT NULL
                unset($validated['price']);
                unset($validated['currency']);
                // Mettre à jour directement avec DB pour forcer null
                DB::table('modules')
                    ->where('id', $module->id)
                    ->update([
                        'price' => null,
                        'currency' => null,
                    ]);
            } else {
                // Si optionnel mais pas de prix, mettre currency par défaut
                if (empty($validated['currency'])) {
                    $validated['currency'] = 'XOF';
                }
            }
        } else {
            $validated['is_optional'] = false;
            // Retirer price et currency du tableau pour éviter les erreurs NOT NULL
            unset($validated['price']);
            unset($validated['currency']);
            // Mettre à jour directement avec DB pour forcer null
            DB::table('modules')
                ->where('id', $module->id)
                ->update([
                    'price' => null,
                    'currency' => null,
                ]);
        }

        // Mettre à jour les autres champs
        $module->update($validated);

        Log::info('Module mis à jour', [
            'admin_id' => $admin->id,
            'module_id' => $module->id,
            'module_data' => $validated,
        ]);

        return redirect()
            ->route('platform-admin.modules.index')
            ->with('success', "Le module \"{$module->name}\" a été mis à jour avec succès.");
    }
}
