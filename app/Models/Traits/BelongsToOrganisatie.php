<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Organisatie;

trait BelongsToOrganisatie
{
    /**
     * Boot de trait en registreer global scopes
     */
    protected static function bootBelongsToOrganisatie(): void
    {
        // Automatisch filteren op organisatie_id voor niet-superadmins
        static::addGlobalScope('organisatie', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organisatie_id && !auth()->user()->isSuperAdmin()) {
                $builder->where('organisatie_id', auth()->user()->organisatie_id);
            }
        });

        // Automatisch organisatie_id toewijzen bij het aanmaken van nieuwe records
        static::creating(function (Model $model) {
            if (auth()->check() && !$model->organisatie_id) {
                $model->organisatie_id = auth()->user()->organisatie_id;
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
