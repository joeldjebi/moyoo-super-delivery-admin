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
        'price',
        'currency',
        'is_optional',
    ];

    protected $casts = [
        'routes' => 'array',
        'is_active' => 'boolean',
        'is_optional' => 'boolean',
        'sort_order' => 'integer',
        'price' => 'decimal:2',
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

    /**
     * Scope pour les modules optionnels
     */
    public function scopeOptional($query)
    {
        return $query->where('is_optional', true);
    }

    /**
     * Scope pour les modules non optionnels (inclus dans le plan)
     */
    public function scopeIncluded($query)
    {
        return $query->where('is_optional', false);
    }

    /**
     * Vérifier si le module est optionnel
     */
    public function isOptional(): bool
    {
        return $this->is_optional === true;
    }

    /**
     * Obtenir le prix formaté
     */
    public function getFormattedPrice(): string
    {
        if (!$this->price) {
            return 'Gratuit';
        }
        return number_format($this->price, 0, ',', ' ') . ' ' . $this->currency;
    }
}
