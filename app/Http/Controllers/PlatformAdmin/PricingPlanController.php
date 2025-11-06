<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PricingPlanController extends Controller
{
    public function index(Request $request)
    {
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

        $data['plans'] = $query->orderBy('sort_order', 'asc')
                      ->orderBy('created_at', 'desc')
                      ->paginate(15);

        return view('platform-admin.pricing-plans.index', $data);
    }

    public function create()
    {
        $data['title'] = 'Créer un plan tarifaire';
        $data['menu'] = 'pricing-plans';
        return view('platform-admin.pricing-plans.create', $data);
    }

    public function store(Request $request)
    {
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
        $plan = DB::table('pricing_plans')->where('id', $id)->first();

        if (!$plan) {
            abort(404);
        }

        return view('platform-admin.pricing-plans.show', compact('plan'));
    }

    public function edit(string $id)
    {
        $plan = DB::table('pricing_plans')->where('id', $id)->first();

        if (!$plan) {
            abort(404);
        }

        return view('platform-admin.pricing-plans.edit', compact('plan'));
    }

    public function update(Request $request, string $id)
    {
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
        DB::table('pricing_plans')->where('id', $id)->delete();

        Log::info('Plan tarifaire supprimé par super admin', [
            'admin_id' => auth()->guard('platform_admin')->id(),
            'plan_id' => $id,
        ]);

        return redirect()->route('platform-admin.pricing-plans.index')
            ->with('success', 'Plan tarifaire supprimé avec succès.');
    }
}