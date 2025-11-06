<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoriqueLivraison extends Model
{
    use SoftDeletes;

    protected $table = 'historique_livraisons';

    protected $fillable = [
        'entreprise_id',
        'package_colis_id',
        'livraison_id',
        'status',
        'code_validation_utilise',
        'photo_proof_path',
        'signature_data',
        'note_livraison',
        'motif_annulation',
        'date_livraison_effective',
        'latitude',
        'longitude',
        'colis_id',
        'livreur_id',
        'montant_a_encaisse',
        'prix_de_vente',
        'montant_de_la_livraison',
        'created_by',
    ];

    protected $casts = [
        'entreprise_id' => 'integer',
        'package_colis_id' => 'integer',
        'livraison_id' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'colis_id' => 'integer',
        'livreur_id' => 'integer',
        'montant_a_encaisse' => 'integer',
        'prix_de_vente' => 'integer',
        'montant_de_la_livraison' => 'integer',
        'date_livraison_effective' => 'datetime',
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
     * Relation avec le colis
     */
    public function colis(): BelongsTo
    {
        return $this->belongsTo(Colis::class, 'colis_id');
    }

    /**
     * Scope pour les livraisons terminées
     */
    public function scopeTermine($query)
    {
        return $query->where('status', 'termine');
    }

    /**
     * Scope pour les livraisons en cours
     */
    public function scopeEnCours($query)
    {
        return $query->where('status', 'en_cours');
    }

    /**
     * Scope pour les livraisons annulées
     */
    public function scopeAnnule($query)
    {
        return $query->where('status', 'annule');
    }

    /**
     * Scope pour les livraisons échouées
     */
    public function scopeEchoue($query)
    {
        return $query->where('status', 'echoue');
    }
}
