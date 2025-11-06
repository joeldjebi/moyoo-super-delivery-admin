<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPlan extends Model
{
    protected $table = 'subscription_plans';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'duration_days',
        'features',
        'max_colis_per_month',
        'max_livreurs',
        'max_marchands',
        'whatsapp_notifications',
        'firebase_notifications',
        'api_access',
        'advanced_reports',
        'priority_support',
        'is_active',
        'sort_order',
        'entreprise_id',
        'whatsapp_sms_limit',
        'pricing_plan_id',
        'started_at',
        'expires_at',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'duration_days' => 'integer',
        'max_colis_per_month' => 'integer',
        'max_livreurs' => 'integer',
        'max_marchands' => 'integer',
        'whatsapp_sms_limit' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'whatsapp_notifications' => 'boolean',
        'firebase_notifications' => 'boolean',
        'api_access' => 'boolean',
        'advanced_reports' => 'boolean',
        'priority_support' => 'boolean',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec l'entreprise
     */
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    /**
     * Relation avec le plan tarifaire
     */
    public function pricingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'pricing_plan_id');
    }

    /**
     * Scope pour les abonnements actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les abonnements expirés
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
                    ->whereNotNull('expires_at');
    }

    /**
     * Scope pour les abonnements non expirés
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->where('expires_at', '>=', now())
              ->orWhereNull('expires_at');
        });
    }
}
