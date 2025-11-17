<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingsZonesTemplate extends Model
{
    protected $fillable = [
        'organisatie_id',
        'naam',
        'sport_type',
        'berekening_basis',
        'beschrijving',
        'is_actief',
        'is_systeem',
    ];

    protected $casts = [
        'is_actief' => 'boolean',
        'is_systeem' => 'boolean',
    ];

    /**
     * Relatie: Template hoort bij een organisatie
     */
    public function organisatie(): BelongsTo
    {
        return $this->belongsTo(Organisatie::class);
    }

    /**
     * Relatie: Template heeft meerdere zones
     */
    public function zones(): HasMany
    {
        return $this->hasMany(TrainingsZone::class, 'template_id')->orderBy('volgorde');
    }
}
