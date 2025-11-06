<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Livraison extends Model
{
    use SoftDeletes;

    protected $table = 'livraisons';

    protected $fillable = [
        'uuid',
        'numero_de_livraison',
        'colis_id',
        'package_colis_id',
        'marchand_id',
        'boutique_id',
        'adresse_de_livraison',
        'status',
        'note_livraison',
        'code_validation',
        'created_by',
    ];

    protected $casts = [
        'colis_id' => 'integer',
        'package_colis_id' => 'integer',
        'marchand_id' => 'integer',
        'boutique_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relation avec l'entreprise via le marchand
     */
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    /**
     * Relation avec le colis
     */
    public function colis(): BelongsTo
    {
        return $this->belongsTo(Colis::class, 'colis_id');
    }

    /**
     * Relation avec le marchand
     */
    public function marchand(): BelongsTo
    {
        return $this->belongsTo(Marchand::class, 'marchand_id');
    }

    /**
     * Relation avec la boutique
     */
    public function boutique(): BelongsTo
    {
        return $this->belongsTo(Boutique::class, 'boutique_id');
    }

    /**
     * Relation avec package_colis
     */
    public function packageColis(): BelongsTo
    {
        return $this->belongsTo(PackageColis::class, 'package_colis_id');
    }

    /**
     * Scope pour les livraisons terminées (status = 2)
     */
    public function scopeTermine($query)
    {
        return $query->where('status', 2);
    }

    /**
     * Scope pour les livraisons en cours (status = 1)
     */
    public function scopeEnCours($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope pour les livraisons annulées (status = 3, 4, 5)
     */
    public function scopeAnnule($query)
    {
        return $query->whereIn('status', [3, 4, 5]);
    }

    /**
     * Scope pour les livraisons en attente (status = 0)
     */
    public function scopeEnAttente($query)
    {
        return $query->where('status', 0);
    }
}
