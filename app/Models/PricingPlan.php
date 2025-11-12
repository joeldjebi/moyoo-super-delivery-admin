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

    /**
     * Attacher un module au plan
     */
    public function attachModule(int $moduleId, bool $isEnabled = true, ?array $limits = null): void
    {
        // Vérifier si le module est déjà attaché
        if ($this->modules()->where('modules.id', $moduleId)->exists()) {
            // Mettre à jour le pivot existant
            $this->modules()->updateExistingPivot($moduleId, [
                'is_enabled' => $isEnabled,
                'limits' => $limits ? json_encode($limits) : null,
            ]);
        } else {
            // Attacher le module
            $this->modules()->attach($moduleId, [
                'is_enabled' => $isEnabled,
                'limits' => $limits ? json_encode($limits) : null,
            ]);
        }
    }

    /**
     * Détacher un module du plan
     */
    public function detachModule(int $moduleId): void
    {
        $this->modules()->detach($moduleId);
    }

    /**
     * Activer/désactiver un module pour le plan
     */
    public function toggleModule(int $moduleId): bool
    {
        $module = $this->modules()->where('modules.id', $moduleId)->first();

        if (!$module) {
            return false;
        }

        $isEnabled = !$module->pivot->is_enabled;

        $this->modules()->updateExistingPivot($moduleId, [
            'is_enabled' => $isEnabled,
        ]);

        return $isEnabled;
    }

    /**
     * Configurer les limites d'un module
     */
    public function configureModule(int $moduleId, array $limits): void
    {
        $this->modules()->updateExistingPivot($moduleId, [
            'limits' => json_encode($limits),
        ]);
    }
}
