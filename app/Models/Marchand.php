<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marchand extends Model
{
    use SoftDeletes;

    protected $table = 'marchands';

    protected $fillable = [
        'entreprise_id',
        'first_name',
        'last_name',
        'mobile',
        'email',
        'adresse',
        'status',
        'commune_id',
        'created_by',
    ];

    protected $casts = [
        'entreprise_id' => 'integer',
        'commune_id' => 'integer',
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
     * Relation avec la commune
     */
    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    /**
     * Relation avec les boutiques
     */
    public function boutiques(): HasMany
    {
        return $this->hasMany(Boutique::class, 'marchand_id');
    }

    /**
     * Scope pour les marchands actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Obtenir le nom complet
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
