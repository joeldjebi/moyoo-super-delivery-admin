<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system_role',
    ];

    protected $casts = [
        'is_system_role' => 'boolean',
    ];

    /**
     * Relation avec les admins (many-to-many)
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(PlatformAdmin::class, 'platform_admin_roles', 'role_id', 'platform_admin_id')
            ->withTimestamps();
    }

    /**
     * Relation avec les permissions (many-to-many)
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
            ->withTimestamps();
    }

    /**
     * Vérifier si le rôle est un rôle système
     */
    public function isSystemRole(): bool
    {
        return $this->is_system_role === true;
    }

    /**
     * Vérifier si le rôle peut être supprimé
     */
    public function canBeDeleted(): bool
    {
        return !$this->isSystemRole();
    }

    /**
     * Scope pour les rôles système
     */
    public function scopeSystemRoles($query)
    {
        return $query->where('is_system_role', true);
    }

    /**
     * Scope pour les rôles non système
     */
    public function scopeNonSystemRoles($query)
    {
        return $query->where('is_system_role', false);
    }
}
