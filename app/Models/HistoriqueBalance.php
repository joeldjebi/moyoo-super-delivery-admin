<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriqueBalance extends Model
{
    protected $table = 'historique_balance';

    protected $fillable = [
        'balance_marchand_id',
        'entreprise_id',
        'type_operation',
        'montant',
        'balance_avant',
        'balance_apres',
        'description',
        'reference',
        'created_by',
    ];

    protected $casts = [
        'balance_marchand_id' => 'integer',
        'entreprise_id' => 'integer',
        'montant' => 'decimal:2',
        'balance_avant' => 'decimal:2',
        'balance_apres' => 'decimal:2',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec la balance marchand
     */
    public function balanceMarchand(): BelongsTo
    {
        return $this->belongsTo(BalanceMarchand::class, 'balance_marchand_id');
    }

    /**
     * Relation avec l'entreprise
     */
    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    /**
     * Relation avec l'utilisateur qui a créé l'historique
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

