<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BalanceMarchand extends Model
{
    protected $table = 'balance_marchands';

    protected $fillable = [
        'entreprise_id',
        'marchand_id',
        'boutique_id',
        'montant_encaisse',
        'montant_reverse',
        'balance_actuelle',
        'derniere_mise_a_jour',
    ];

    protected $casts = [
        'entreprise_id' => 'integer',
        'marchand_id' => 'integer',
        'boutique_id' => 'integer',
        'montant_encaisse' => 'decimal:2',
        'montant_reverse' => 'decimal:2',
        'balance_actuelle' => 'decimal:2',
        'derniere_mise_a_jour' => 'datetime',
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
     * Relation avec l'historique de balance
     */
    public function historique(): HasMany
    {
        return $this->hasMany(HistoriqueBalance::class, 'balance_marchand_id');
    }
}

