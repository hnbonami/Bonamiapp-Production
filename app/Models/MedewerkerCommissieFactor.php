<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedewerkerCommissieFactor extends Model
{
    protected $table = 'medewerker_commissie_factoren';
    
    protected $fillable = [
        'user_id',
        'dienst_id',
        'diploma_factor',
        'ervaring_factor',
        'ancienniteit_factor',
        'custom_commissie_percentage',
        'opmerking',
        'is_actief',
    ];

    protected $casts = [
        'diploma_factor' => 'decimal:2',
        'ervaring_factor' => 'decimal:2',
        'ancienniteit_factor' => 'decimal:2',
        'custom_commissie_percentage' => 'decimal:2',
        'is_actief' => 'boolean',
    ];

    /**
     * Relatie met User (medewerker)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relatie met Dienst (optioneel voor dienst-specifieke commissie)
     */
    public function dienst(): BelongsTo
    {
        return $this->belongsTo(Dienst::class);
    }

    /**
     * Bereken totale bonus percentage (diploma + ervaring + anciënniteit)
     */
    public function getTotaleBonusAttribute(): float
    {
        return (float) ($this->diploma_factor + $this->ervaring_factor + $this->ancienniteit_factor);
    }

    /**
     * Check of dit een algemene factor is (geen specifieke dienst)
     */
    public function isAlgemeen(): bool
    {
        return $this->dienst_id === null;
    }

    /**
     * Check of dit een dienst-specifieke override is
     */
    public function isDienstSpecifiek(): bool
    {
        return $this->dienst_id !== null;
    }

    /**
     * Scope: alleen actieve factoren
     */
    public function scopeActief($query)
    {
        return $query->where('is_actief', true);
    }

    /**
     * Scope: alleen algemene factoren (zonder dienst)
     */
    public function scopeAlgemeen($query)
    {
        return $query->whereNull('dienst_id');
    }

    /**
     * Scope: alleen dienst-specifieke factoren
     */
    public function scopeDienstSpecifiek($query)
    {
        return $query->whereNotNull('dienst_id');
    }
}
