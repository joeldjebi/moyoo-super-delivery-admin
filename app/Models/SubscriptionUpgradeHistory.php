<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionUpgradeHistory extends Model
{
    use SoftDeletes;

    protected $table = 'subscription_upgrade_history';

    protected $fillable = [
        'entreprise_id',
        'ancien_subscription_plan_id',
        'nouveau_subscription_plan_id',
        'ancien_pricing_plan_id',
        'nouveau_pricing_plan_id',
        'upgraded_by',
        'ancien_prix',
        'nouveau_prix',
        'ancien_currency',
        'nouveau_currency',
        'raison',
        'notes',
        'document',
        'date_upgrade',
    ];

    protected $casts = [
        'ancien_prix' => 'decimal:2',
        'nouveau_prix' => 'decimal:2',
        'date_upgrade' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relation avec l'entreprise
     */
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    /**
     * Relation avec l'ancien abonnement
     */
    public function ancienSubscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'ancien_subscription_plan_id');
    }

    /**
     * Relation avec le nouveau abonnement
     */
    public function nouveauSubscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'nouveau_subscription_plan_id');
    }

    /**
     * Relation avec l'ancien plan tarifaire
     */
    public function ancienPricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'ancien_pricing_plan_id');
    }

    /**
     * Relation avec le nouveau plan tarifaire
     */
    public function nouveauPricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'nouveau_pricing_plan_id');
    }

    /**
     * Relation avec le super admin qui a effectué l'upgrade
     */
    public function upgradedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PlatformAdmin::class, 'upgraded_by');
    }
}
