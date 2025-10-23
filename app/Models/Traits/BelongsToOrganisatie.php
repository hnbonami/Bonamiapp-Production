<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Organisatie;

trait BelongsToOrganisatie
{
    /**
     * Boot de trait - voeg global scope toe voor automatische filtering
     */
    protected static function bootBelongsToOrganisatie()
    {
        // Voeg global scope toe om automatisch te filteren op organisatie_id
        static::addGlobalScope('organisatie', function (Builder $builder) {
            // Alleen filteren als gebruiker is ingelogd
            if (auth()->check() && auth()->user()->organisatie_id) {
                $builder->where($builder->getModel()->getTable() . '.organisatie_id', auth()->user()->organisatie_id);
            }
        });
    }

    /**
     * Relatie: model behoort tot een organisatie
     */
    public function organisatie(): BelongsTo
    {
        return $this->belongsTo(Organisatie::class, 'organisatie_id');
    }

    /**
     * Scope: haal alle records op zonder organisatie filter (voor superadmin)
     */
    public function scopeAlleOrganisaties(Builder $query): Builder
    {
        return $query->withoutGlobalScope('organisatie');
    }

    /**
     * Scope: filter op specifieke organisatie
     */
    public function scopeVoorOrganisatie(Builder $query, int $organisatieId): Builder
    {
        return $query->withoutGlobalScope('organisatie')
            ->where('organisatie_id', $organisatieId);
    }
}
