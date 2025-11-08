<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
     * Obtenir les permissions avec fallback si la relation ne fonctionne pas
     */
    public function getPermissionsWithFallback()
    {
        // Essayer d'abord la relation normale
        $permissions = $this->permissions;

        if ($permissions->count() > 0) {
            return $permissions;
        }

        // Si la relation ne fonctionne pas, charger manuellement
        $hasRoleId = Schema::hasColumn('role_permissions', 'role_id');

        if ($hasRoleId) {
            $permissionIds = DB::table('role_permissions')
                ->where('role_id', $this->id)
                ->pluck('permission_id');
        } else {
            // Utiliser la colonne 'role' si role_id n'existe pas
            $permissionIds = DB::table('role_permissions')
                ->where('role', $this->slug)
                ->orWhere('role', $this->name)
                ->pluck('permission_id');
        }

        return Permission::whereIn('id', $permissionIds)->get();
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
