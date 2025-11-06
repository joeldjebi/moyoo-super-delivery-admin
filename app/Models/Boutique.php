<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boutique extends Model
{
    use SoftDeletes;

    protected $table = 'boutiques';

    protected $fillable = [
        'entreprise_id',
        'libelle',
        'mobile',
        'adresse',
        'adresse_gps',
        'cover_image',
        'marchand_id',
        'status',
        'created_by',
    ];

    protected $casts = [
        'entreprise_id' => 'integer',
        'marchand_id' => 'integer',
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
     * Relation avec le marchand
     */
    public function marchand(): BelongsTo
    {
        return $this->belongsTo(Marchand::class, 'marchand_id');
    }

    /**
     * Scope pour les boutiques actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
