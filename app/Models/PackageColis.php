<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageColis extends Model
{

    protected $table = 'package_colis';

    protected $fillable = [
        'entreprise_id',
        'boutique_id',
        'name',
        'status',
        'created_by',
    ];

    protected $casts = [
        'entreprise_id' => 'integer',
        'boutique_id' => 'integer',
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
     * Relation avec la boutique
     */
    public function boutique(): BelongsTo
    {
        return $this->belongsTo(Boutique::class, 'boutique_id');
    }

    /**
     * Relation avec les colis
     */
    public function colis(): HasMany
    {
        return $this->hasMany(Colis::class, 'package_colis_id');
    }
}
