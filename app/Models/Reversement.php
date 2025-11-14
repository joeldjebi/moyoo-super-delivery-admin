<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reversement extends Model
{
    protected $table = 'reversements';

    protected $fillable = [
        'entreprise_id',
        'marchand_id',
        'boutique_id',
        'montant_reverse',
        'mode_reversement',
        'reference_reversement',
        'statut',
        'date_reversement',
        'notes',
        'justificatif_path',
        'created_by',
        'validated_by',
    ];

    protected $casts = [
        'entreprise_id' => 'integer',
        'marchand_id' => 'integer',
        'boutique_id' => 'integer',
        'montant_reverse' => 'decimal:2',
        'date_reversement' => 'datetime',
        'created_by' => 'integer',
        'validated_by' => 'integer',
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
     * Relation avec l'utilisateur qui a créé le reversement
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur qui a validé le reversement
     */
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}

