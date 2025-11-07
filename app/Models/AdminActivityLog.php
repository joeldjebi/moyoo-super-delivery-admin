<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivityLog extends Model
{
    protected $table = 'admin_activity_logs';

    protected $fillable = [
        'platform_admin_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Relation avec l'admin
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'platform_admin_id');
    }

    /**
     * Relation polymorphique avec le modèle concerné
     */
    public function model()
    {
        return $this->morphTo('model');
    }

    /**
     * Scope pour filtrer par action
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope pour filtrer par modèle
     */
    public function scopeForModel($query, string $modelType, ?int $modelId = null)
    {
        $query->where('model_type', $modelType);
        if ($modelId !== null) {
            $query->where('model_id', $modelId);
        }
        return $query;
    }

    /**
     * Scope pour filtrer par admin
     */
    public function scopeForAdmin($query, int $adminId)
    {
        return $query->where('platform_admin_id', $adminId);
    }
}
