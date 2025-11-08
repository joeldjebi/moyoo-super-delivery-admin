<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PricingPlan extends Model
{
    protected $table = 'pricing_plans';

    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'period',
        'features',
        'is_popular',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec les modules (many-to-many)
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'pricing_plan_modules', 'pricing_plan_id', 'module_id')
            ->withPivot('is_enabled', 'limits')
            ->withTimestamps();
    }

    /**
     * Scope pour les plans actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les plans populaires
     */
    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    /**
     * Vérifier si le plan a un module
     */
    public function hasModule(string $moduleSlug): bool
    {
        return $this->modules()
            ->where('modules.slug', $moduleSlug)
            ->where('pricing_plan_modules.is_enabled', true)
            ->exists();
    }

    /**
     * Obtenir les limites d'un module
     */
    public function getModuleLimits(string $moduleSlug): ?array
    {
        $pivot = $this->modules()
            ->where('modules.slug', $moduleSlug)
            ->first();

        return $pivot && $pivot->pivot->limits ? $pivot->pivot->limits : null;
    }
}
