<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entreprise extends Model
{
    use SoftDeletes;

    protected $table = 'entreprises';

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'adresse',
        'commune_id',
        'logo',
        'statut',
        'created_by',
        'not_update',
    ];

    protected $casts = [
        'statut' => 'integer',
        'commune_id' => 'integer',
        'created_by' => 'integer',
        'not_update' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Scope pour les entreprises actives
     */
    public function scopeActive($query)
    {
        return $query->where('statut', 1);
    }

    /**
     * Scope pour les entreprises inactives
     */
    public function scopeInactive($query)
    {
        return $query->where('statut', 0);
    }
}
