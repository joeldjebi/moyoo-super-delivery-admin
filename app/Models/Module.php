<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'category',
        'is_active',
        'sort_order',
        'routes',
    ];

    protected $casts = [
        'routes' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec les pricing plans (many-to-many)
     */
    public function pricingPlans(): BelongsToMany
    {
        return $this->belongsToMany(PricingPlan::class, 'pricing_plan_modules', 'module_id', 'pricing_plan_id')
            ->withPivot('is_enabled', 'limits')
            ->withTimestamps();
    }

    /**
     * Scope pour les modules actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les modules par catégorie
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope pour les modules core
     */
    public function scopeCore($query)
    {
        return $query->where('category', 'core');
    }

    /**
     * Scope pour les modules premium
     */
    public function scopePremium($query)
    {
        return $query->where('category', 'premium');
    }

    /**
     * Vérifier si le module est activé pour un pricing plan
     */
    public function isEnabledForPricingPlan(int $pricingPlanId): bool
    {
        $pivot = $this->pricingPlans()
            ->where('pricing_plans.id', $pricingPlanId)
            ->first();

        return $pivot && $pivot->pivot->is_enabled;
    }

    /**
     * Obtenir les limites d'un module pour un pricing plan
     */
    public function getLimitsForPricingPlan(int $pricingPlanId): ?array
    {
        $pivot = $this->pricingPlans()
            ->where('pricing_plans.id', $pricingPlanId)
            ->first();

        return $pivot && $pivot->pivot->limits ? $pivot->pivot->limits : null;
    }
}
