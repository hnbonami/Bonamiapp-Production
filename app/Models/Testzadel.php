<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class Testzadel extends Model
{
    use HasFactory;

    protected $table = 'testzadels';

    protected $fillable = [
        'merk',
        'model', 
        'type',
        'breedte_mm',
        'klant_id',
        'bikefit_id',
        'status',
        'uitgeleend_op',
        'verwachte_retour_datum',
        'teruggegeven_op',
        'automatisch_herinneringsmails_versturen',
        'opmerkingen',
        // Legacy kolommen voor backward compatibility
        'zadel_merk',
        'zadel_model',
        'zadel_type',
        'zadel_breedte',
        'uitleen_datum',
        'werkelijke_retour_datum',
        'automatisch_mailtje',
        'herinnering_verstuurd',
        'herinnering_verstuurd_op',
        'laatste_herinnering'
    ];

    protected $casts = [
        'verwachte_retour_datum' => 'datetime',
        'uitleen_datum' => 'date',
        'werkelijke_retour_datum' => 'datetime',
        'herinnering_verstuurd_op' => 'datetime',
        'laatste_herinnering' => 'datetime',
        'automatisch_mailtje' => 'boolean',
        'automatisch_herinneringsmails_versturen' => 'boolean'
    ];

    /**
     * Accessor voor uitgeleend_op - valt terug op uitleen_datum
     */
    public function getUitgeleendOpAttribute()
    {
        return $this->uitleen_datum ? Carbon::parse($this->uitleen_datum) : null;
    }

    /**
     * Accessor voor teruggegeven_op - valt terug op werkelijke_retour_datum
     */
    public function getTeruggegeven_opAttribute()
    {
        return $this->werkelijke_retour_datum ? Carbon::parse($this->werkelijke_retour_datum) : null;
    }

    /**
     * Accessor voor merk - valt terug op zadel_merk
     */
    public function getMerkAttribute($value)
    {
        return $value ?: $this->zadel_merk;
    }

    /**
     * Accessor voor model - valt terug op zadel_model
     */
    public function getModelAttribute($value)
    {
        return $value ?: $this->zadel_model;
    }

    /**
     * Accessor voor type - valt terug op zadel_type
     */
    public function getTypeAttribute($value)
    {
        return $value ?: $this->zadel_type;
    }

    /**
     * Accessor voor breedte_mm - valt terug op zadel_breedte
     */
    public function getBreedteMmAttribute($value)
    {
        return $value ?: $this->zadel_breedte;
    }

    // Mogelijke statussen voor testzadels
    const STATUS_UITGELEEND = 'uitgeleend';
    const STATUS_TERUGGEGEVEN = 'teruggegeven'; 
    const STATUS_GEARCHIVEERD = 'gearchiveerd';
    
    /**
     * Verkrijg alle mogelijke statussen
     */
    public static function getStatussen()
    {
        return [
            self::STATUS_UITGELEEND => 'Uitgeleend',
            self::STATUS_TERUGGEGEVEN => 'Teruggegeven',
            self::STATUS_GEARCHIVEERD => 'Gearchiveerd'
        ];
    }
    
    /**
     * Check of testzadel uitgeleend is
     */
    public function isUitgeleend()
    {
        return $this->status === self::STATUS_UITGELEEND;
    }
    
    /**
     * Check of testzadel te laat is
     */
    public function isTeLaat()
    {
        return $this->isUitgeleend() && 
               $this->verwachte_retour_datum && 
               $this->verwachte_retour_datum->isPast();
    }
    
    /**
     * Fix voor bestaande testzadels met status "nieuw"
     */
    public static function fixNieuweStatussen()
    {
        $testzadelsMetNieuweStatus = self::where('status', 'nieuw')->get();
        
        foreach ($testzadelsMetNieuweStatus as $testzadel) {
            // Als er een klant gekoppeld is, waarschijnlijk uitgeleend
            if ($testzadel->klant_id) {
                $testzadel->update([
                    'status' => self::STATUS_UITGELEEND,
                ]);
                \Log::info("Testzadel {$testzadel->id} status gefixed van 'nieuw' naar 'uitgeleend'");
            } else {
                // Geen klant, waarschijnlijk teruggegeven
                $testzadel->update([
                    'status' => self::STATUS_TERUGGEGEVEN,
                ]);
                \Log::info("Testzadel {$testzadel->id} status gefixed van 'nieuw' naar 'teruggegeven'");
            }
        }
        
        return $testzadelsMetNieuweStatus->count();
    }

    /**
     * Relationships
     */
    public function klant()
    {
        return $this->belongsTo(Klant::class);
    }

    public function bikefit()
    {
        return $this->belongsTo(Bikefit::class);
    }

    /**
     * Scopes
     */
    public function scopeUitgeleend($query)
    {
        return $query->where('status', self::STATUS_UITGELEEND);
    }

    public function scopeTeLaat($query)
    {
        return $query->where('status', self::STATUS_UITGELEEND)
                    ->where('verwachte_retour_datum', '<', now());
    }

    public function scopeTeruggegeven($query)
    {
        return $query->where('status', self::STATUS_TERUGGEGEVEN);
    }

    public function scopeGearchiveerd($query)
    {
        return $query->where('status', self::STATUS_GEARCHIVEERD);
    }
}