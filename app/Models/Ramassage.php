<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ramassage extends Model
{
    protected $table = 'ramassages';

    protected $fillable = [
        'code_ramassage',
        'entreprise_id',
        'marchand_id',
        'boutique_id',
        'date_demande',
        'date_planifiee',
        'date_effectuee',
        'statut',
        'raison_annulation',
        'commentaire_annulation',
        'date_annulation',
        'annule_par',
        'adresse_ramassage',
        'contact_ramassage',
        'nombre_colis_estime',
        'nombre_colis_reel',
        'difference_colis',
        'type_difference',
        'raison_difference',
        'livreur_id',
        'date_debut_ramassage',
        'date_fin_ramassage',
        'photo_ramassage',
        'notes_livreur',
        'notes_ramassage',
        'notes',
        'colis_data',
        'montant_total',
    ];

    protected $casts = [
        'entreprise_id' => 'integer',
        'marchand_id' => 'integer',
        'boutique_id' => 'integer',
        'nombre_colis_estime' => 'integer',
        'nombre_colis_reel' => 'integer',
        'difference_colis' => 'integer',
        'livreur_id' => 'integer',
        'annule_par' => 'integer',
        'montant_total' => 'decimal:2',
        'colis_data' => 'array',
        'date_demande' => 'datetime',
        'date_planifiee' => 'datetime',
        'date_effectuee' => 'date',
        'date_annulation' => 'datetime',
        'date_debut_ramassage' => 'datetime',
        'date_fin_ramassage' => 'datetime',
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
     * Relation avec le livreur
     */
    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class, 'livreur_id');
    }

    /**
     * Relation avec les planifications
     */
    public function planifications(): HasMany
    {
        return $this->hasMany(PlanificationRamassage::class, 'ramassage_id');
    }

    /**
     * Scope pour les ramassages terminés
     */
    public function scopeTermine($query)
    {
        return $query->where('statut', 'termine');
    }

    /**
     * Scope pour les ramassages en cours
     */
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }

    /**
     * Scope pour les ramassages annulés
     */
    public function scopeAnnule($query)
    {
        return $query->where('statut', 'annule');
    }

    /**
     * Scope pour les ramassages planifiés
     */
    public function scopePlanifie($query)
    {
        return $query->where('statut', 'planifie');
    }
}
