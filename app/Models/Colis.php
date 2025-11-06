<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Colis extends Model
{
    use SoftDeletes;

    protected $table = 'colis';

    protected $fillable = [
        'entreprise_id',
        'package_colis_id',
        'uuid',
        'code',
        'status',
        'nom_client',
        'telephone_client',
        'adresse_client',
        'montant_a_encaisse',
        'prix_de_vente',
        'frais_livraison',
        'numero_facture',
        'note_client',
        'instructions_livraison',
        'zone_id',
        'commune_id',
        'ordre_livraison',
        'date_livraison_prevue',
        'livreur_id',
        'engin_id',
        'poids_id',
        'mode_livraison_id',
        'temp_id',
        'created_by',
    ];

    protected $casts = [
        'entreprise_id' => 'integer',
        'package_colis_id' => 'integer',
        'status' => 'integer',
        'montant_a_encaisse' => 'decimal:2',
        'prix_de_vente' => 'decimal:2',
        'frais_livraison' => 'decimal:2',
        'zone_id' => 'integer',
        'commune_id' => 'integer',
        'ordre_livraison' => 'integer',
        'date_livraison_prevue' => 'datetime',
        'livreur_id' => 'integer',
        'engin_id' => 'integer',
        'poids_id' => 'integer',
        'mode_livraison_id' => 'integer',
        'temp_id' => 'integer',
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
     * Relation avec package_colis
     */
    public function packageColis(): BelongsTo
    {
        return $this->belongsTo(PackageColis::class, 'package_colis_id');
    }

    /**
     * Scope pour les colis terminés (status = 2)
     */
    public function scopeTermine($query)
    {
        return $query->where('status', 2);
    }

    /**
     * Scope pour les colis en cours (status = 1)
     */
    public function scopeEnCours($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope pour les colis annulés (status = 3, 4, 5)
     */
    public function scopeAnnule($query)
    {
        return $query->whereIn('status', [3, 4, 5]);
    }

    /**
     * Scope pour les colis en attente (status = 0)
     */
    public function scopeEnAttente($query)
    {
        return $query->where('status', 0);
    }
}
