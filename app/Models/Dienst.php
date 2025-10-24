<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dienst extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'diensten';

    protected $fillable = [
        'organisatie_id', // VOEG TOE
        'naam',
        'omschrijving',
        'standaard_prijs',
        'btw_percentage',
        'prijs_incl_btw',
        'prijs_excl_btw',
        'commissie_percentage',
        'is_actief',
        'sorteer_volgorde',
    ];

    protected $casts = [
        'standaard_prijs' => 'decimal:2',
        'btw_percentage' => 'decimal:2',
        'prijs_incl_btw' => 'decimal:2',
        'prijs_excl_btw' => 'decimal:2',
        'commissie_percentage' => 'decimal:2',
        'is_actief' => 'boolean',
        'sorteer_volgorde' => 'integer',
    ];

    /**
     * Coaches die deze dienst kunnen uitvoeren
     */
    public function coaches()
    {
        return $this->belongsToMany(User::class, 'coach_diensten', 'dienst_id', 'user_id')
                    ->withPivot('custom_prijs', 'commissie_percentage', 'is_actief')
                    ->withTimestamps();
    }

    /**
     * Prestaties voor deze dienst
     */
    public function prestaties()
    {
        return $this->hasMany(Prestatie::class);
    }

    /**
     * Scope voor alleen actieve diensten
     */
    public function scopeActief($query)
    {
        return $query->where('is_actief', true);
    }

    /**
     * Bereken prijs exclusief BTW op basis van prijs inclusief BTW
     */
    public function berekenPrijsExclBtw(): float
    {
        if ($this->prijs_incl_btw && $this->btw_percentage > 0) {
            return $this->prijs_incl_btw / (1 + ($this->btw_percentage / 100));
        }
        return $this->prijs_excl_btw ?? 0;
    }

    /**
     * Bereken prijs inclusief BTW op basis van prijs exclusief BTW
     */
    public function berekenPrijsInclBtw(): float
    {
        if ($this->prijs_excl_btw && $this->btw_percentage > 0) {
            return $this->prijs_excl_btw * (1 + ($this->btw_percentage / 100));
        }
        return $this->prijs_incl_btw ?? 0;
    }

    /**
     * Bereken BTW bedrag
     */
    public function berekenBtwBedrag(): float
    {
        if ($this->prijs_incl_btw && $this->prijs_excl_btw) {
            return $this->prijs_incl_btw - $this->prijs_excl_btw;
        }
        return 0;
    }
}
