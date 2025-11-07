<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'resource',
        'action',
        'description',
    ];

    /**
     * Relation avec les rôles (many-to-many)
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * Relation avec les admins (many-to-many - permissions directes)
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(PlatformAdmin::class, 'platform_admin_permissions', 'permission_id', 'platform_admin_id')
            ->withTimestamps();
    }

    /**
     * Obtenir le nom complet de la permission (resource.action)
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->resource}.{$this->action}";
    }

    /**
     * Scope pour filtrer par ressource
     */
    public function scopeForResource($query, string $resource)
    {
        return $query->where('resource', $resource);
    }

    /**
     * Scope pour filtrer par action
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
