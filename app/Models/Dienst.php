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
        'naam',
        'beschrijving',
        'standaard_prijs',
        'btw_percentage',
        'is_actief',
        'sorteer_volgorde',
    ];

    protected $casts = [
        'standaard_prijs' => 'decimal:2',
        'btw_percentage' => 'decimal:2',
        'is_actief' => 'boolean',
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
     * Bereken netto prijs (bruto - btw)
     */
    public function berekenNettoPrijs(): float
    {
        $btwBedrag = $this->standaard_prijs * ($this->btw_percentage / 100);
        return round($this->standaard_prijs - $btwBedrag, 2);
    }
}
