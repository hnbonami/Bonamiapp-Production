<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'naam',
        'beschrijving',
        'categorie',
        'icoon',
        'is_premium',
        'prijs_per_maand',
        'is_actief',
        'sorteer_volgorde',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'is_actief' => 'boolean',
        'prijs_per_maand' => 'decimal:2',
    ];

    /**
     * Organisaties die deze feature hebben
     */
    public function organisaties()
    {
        return $this->belongsToMany(Organisatie::class, 'organisatie_features')
            ->withPivot(['expires_at', 'is_actief', 'notities'])
            ->withTimestamps();
    }

    /**
     * Scope voor alleen actieve features
     */
    public function scopeActief($query)
    {
        return $query->where('is_actief', true);
    }

    /**
     * Scope voor premium features
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope voor gratis features
     */
    public function scopeGratis($query)
    {
        return $query->where('is_premium', false);
    }

    /**
     * Groepeer features per categorie
     */
    public static function perCategorie()
    {
        return self::actief()
            ->orderBy('sorteer_volgorde')
            ->get()
            ->groupBy('categorie');
    }
}

// Check of Feature model bestaat
