<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingsZone extends Model
{
    protected $fillable = [
        'template_id',
        'zone_naam',
        'kleur',
        'min_percentage',
        'max_percentage',
        'referentie_waarde',
        'volgorde',
        'beschrijving',
    ];

    protected $casts = [
        'min_percentage' => 'integer',
        'max_percentage' => 'integer',
        'volgorde' => 'integer',
    ];

    /**
     * Relatie: Zone hoort bij een template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(TrainingsZonesTemplate::class, 'template_id');
    }
}
