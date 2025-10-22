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
        // Voeg global scope toe die automatisch filtert op organisatie
        static::addGlobalScope('organisatie', function ($query) {
            $user = auth()->user();
            
            // Superadmin ziet alles
            if ($user && $user->isSuperAdmin()) {
                return;
            }
            
            // Andere users zien alleen eigen organisatie data
            if ($user && $user->organisatie_id) {
                // Gebruik $query->getModel()->getTable() om de tabelnaam te krijgen
                $tableName = $query->getModel()->getTable();
                $query->where($tableName . '.organisatie_id', $user->organisatie_id);
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
