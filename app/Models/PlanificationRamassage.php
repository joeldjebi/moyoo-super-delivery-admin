<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanificationRamassage extends Model
{
    protected $table = 'planification_ramassage';

    protected $fillable = [
        'ramassage_id',
        'livreur_id',
        'entreprise_id',
        'date_planifiee',
        'heure_debut',
        'heure_fin',
        'zone_ramassage',
        'ordre_visite',
        'statut_planification',
        'notes_planification',
    ];

    protected $casts = [
        'ramassage_id' => 'integer',
        'livreur_id' => 'integer',
        'entreprise_id' => 'integer',
        'ordre_visite' => 'integer',
        'date_planifiee' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec le ramassage
     */
    public function ramassage(): BelongsTo
    {
        return $this->belongsTo(Ramassage::class, 'ramassage_id');
    }

    /**
     * Relation avec le livreur
     */
    public function livreur(): BelongsTo
    {
        return $this->belongsTo(Livreur::class, 'livreur_id');
    }

    /**
     * Relation avec l'entreprise
     */
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }
}
