<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class PlatformAdmin extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $guard = 'platform_admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'first_name',
        'last_name',
        'password',
        'status',
        'two_factor_enabled',
        'two_factor_secret',
        'last_login_at',
        'last_login_ip',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'failed_login_attempts' => 'integer',
        ];
    }

    /**
     * Get the name of the unique identifier for the user.
     * Cette méthode retourne toujours 'id' car c'est utilisé pour stocker
     * l'identifiant dans la session (table sessions.user_id qui est bigint).
     * Pour l'authentification avec username, on utilise une recherche manuelle.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Vérifier si l'admin est actif
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Vérifier si l'admin est bloqué
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        if ($this->locked_until === null) {
            return false;
        }

        return $this->locked_until->isFuture();
    }

    /**
     * Vérifier si l'admin peut se connecter
     *
     * @return bool
     */
    public function canLogin(): bool
    {
        return $this->isActive() && !$this->isLocked();
    }

    /**
     * Incrémenter les tentatives de connexion échouées
     *
     * @return void
     */
    public function incrementFailedLoginAttempts(): void
    {
        $this->increment('failed_login_attempts');

        // Bloquer après 5 tentatives échouées pendant 30 minutes
        if ($this->failed_login_attempts >= 5) {
            $this->locked_until = now()->addMinutes(30);
            $this->save();
        }
    }

    /**
     * Réinitialiser les tentatives de connexion échouées
     *
     * @return void
     */
    public function resetFailedLoginAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    /**
     * Enregistrer les informations de connexion
     *
     * @param string|null $ip
     * @return void
     */
    public function recordLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    /**
     * Obtenir le nom complet
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name) ?: $this->username;
    }

    /**
     * Relation avec l'admin créateur
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(PlatformAdmin::class, 'created_by');
    }

    /**
     * Relation avec les admins créés par cet admin
     */
    public function createdAdmins(): HasMany
    {
        return $this->hasMany(PlatformAdmin::class, 'created_by');
    }

    /**
     * Relation avec les rôles (many-to-many)
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'platform_admin_roles', 'platform_admin_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * Relation avec les permissions directes (many-to-many)
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'platform_admin_permissions', 'platform_admin_id', 'permission_id')
            ->withTimestamps();
    }

    /**
     * Relation avec les logs d'activité
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class, 'platform_admin_id');
    }

    /**
     * Vérifier si l'admin a un rôle spécifique
     */
    public function hasRole(string|array $role): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $roles = is_array($role) ? $role : [$role];

        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    /**
     * Vérifier si l'admin a au moins un des rôles spécifiés
     */
    public function hasAnyRole(array $roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    /**
     * Vérifier si l'admin est super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->roles()->where('slug', 'super-admin')->exists();
    }

    /**
     * Obtenir toutes les permissions de l'admin (via rôles + permissions directes)
     */
    public function getAllPermissions()
    {
        if ($this->isSuperAdmin()) {
            // Super admin a toutes les permissions
            return Permission::all();
        }

        // Permissions via rôles
        $rolePermissions = Permission::whereHas('roles', function ($query) {
            $query->whereHas('admins', function ($q) {
                $q->where('platform_admins.id', $this->id);
            });
        })->get();

        // Permissions directes
        $directPermissions = $this->permissions;

        // Fusionner et dédupliquer
        return $rolePermissions->merge($directPermissions)->unique('id');
    }

    /**
     * Vérifier si l'admin a une permission spécifique
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Format: resource.action ou resource.action
        $parts = explode('.', $permission);
        if (count($parts) !== 2) {
            return false;
        }

        [$resource, $action] = $parts;

        // Vérifier les permissions directes
        $hasDirectPermission = $this->permissions()
            ->where('resource', $resource)
            ->where('action', $action)
            ->exists();

        if ($hasDirectPermission) {
            return true;
        }

        // Vérifier les permissions via les rôles
        return $this->roles()->whereHas('permissions', function ($query) use ($resource, $action) {
            $query->where('resource', $resource)
                  ->where('action', $action);
        })->exists();
    }

}
