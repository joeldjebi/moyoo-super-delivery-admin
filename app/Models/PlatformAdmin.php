<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
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
}
