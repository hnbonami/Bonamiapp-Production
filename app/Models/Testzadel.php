<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Testzadel extends Model
{
    use HasFactory;

    protected $attributes = [
        'zadel_model' => '',
        'zadel_merk' => '',
        'zadel_type' => '',
        'zadel_breedte' => null,
        'opmerkingen' => '',
    ];

    // Automatically set zadel_model to empty string if null
    public function setZadelModelAttribute($value)
    {
        $this->attributes['zadel_model'] = $value ?? '';
    }

    // Automatically set zadel_merk to empty string if null
    public function setZadelMerkAttribute($value)
    {
        $this->attributes['zadel_merk'] = $value ?? '';
    }

    // Automatically set zadel_type to empty string if null
    public function setZadelTypeAttribute($value)
    {
        $this->attributes['zadel_type'] = $value ?? '';
    }

    // Automatically set zadel_breedte to null if empty (it's an integer field)
    public function setZadelBreedteAttribute($value)
    {
        $this->attributes['zadel_breedte'] = ($value === '' || $value === null) ? null : $value;
    }

    // Automatically set opmerkingen to empty string if null
    public function setOpmerkingenAttribute($value)
    {
        $this->attributes['opmerkingen'] = $value ?? '';
    }

    protected $fillable = [
        'klant_id',
        'bikefit_id',
        // Nieuwe uitleensysteem kolommen
        'onderdeel_type',
        'onderdeel_status', 
        'automatisch_mailtje',
        'onderdeel_omschrijving',
        // Bestaande kolommen (origineel)
        'zadel_merk',
        'zadel_model',
        'zadel_type',
        'zadel_breedte',
        'zadel_beschrijving',
        'foto_path',
        'uitleen_datum',
        'verwachte_retour_datum',
        'werkelijke_retour_datum',
        'status',
        'herinnering_verstuurd',
        'herinnering_verstuurd_op',
        'opmerkingen',
        'gearchiveerd',
        'gearchiveerd_op',
        'laatste_herinnering',
        'feedback'
    ];

    protected $casts = [
        // Bestaande datum kolommen
        'uitleen_datum' => 'date',
        'verwachte_retour_datum' => 'date',
        'werkelijke_retour_datum' => 'date',
        'gearchiveerd_op' => 'datetime',
        'laatste_herinnering' => 'datetime',
        'herinnering_verstuurd_op' => 'datetime',
        // Boolean kolommen
        'automatisch_mailtje' => 'boolean',
        'herinnering_verstuurd' => 'boolean',
        'gearchiveerd' => 'boolean'
    ];

    // Relationships
    public function klant()
    {
        return $this->belongsTo(Klant::class);
    }

    public function bikefit()
    {
        return $this->belongsTo(Bikefit::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('gearchiveerd', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('gearchiveerd', true);
    }

    // Accessors voor backwards compatibility - gebruik bestaande kolommen
    public function getZadelMerkAttribute()
    {
        return $this->attributes['zadel_merk'] ?? null;
    }

    public function getZadelModelAttribute() 
    {
        return $this->attributes['zadel_model'] ?? null;
    }

    public function getZadelTypeAttribute()
    {
        return $this->attributes['zadel_type'] ?? null;
    }

    public function getZadelBreedteAttribute()
    {
        return $this->attributes['zadel_breedte'] ?? null;
    }

    public function getUitleenDatumAttribute()
    {
        return $this->attributes['uitleen_datum'] ?? null;
    }

    public function getVerwachteRetourDatumAttribute()
    {
        return $this->attributes['verwachte_retour_datum'] ?? null;
    }
    
    // Nieuwe accessors die de bestaande kolommen gebruiken voor nieuwe velden
    public function getMerkAttribute()
    {
        return $this->attributes['merk'] ?? $this->attributes['zadel_merk'] ?? null;
    }
    
    public function getModelAttribute()
    {
        return $this->attributes['model'] ?? $this->attributes['zadel_model'] ?? null;
    }
    
    public function getTypeAttribute()
    {
        return $this->attributes['type'] ?? $this->attributes['zadel_type'] ?? null;
    }
    
    public function getBreedteAttribute()
    {
        return $this->attributes['breedte'] ?? $this->attributes['zadel_breedte'] ?? null;
    }
    
    public function getUitgeleendOpAttribute()
    {
        return $this->attributes['uitgeleend_op'] ?? $this->attributes['uitleen_datum'] ?? null;
    }
    
    public function getVerwachteTeugbringDatumAttribute()
    {
        return $this->attributes['verwachte_terugbring_datum'] ?? $this->attributes['verwachte_retour_datum'] ?? null;
    }
    
    public function getBeschrijvingAttribute()
    {
        return $this->attributes['beschrijving'] ?? $this->attributes['zadel_beschrijving'] ?? null;
    }
    
    public function getFotoPadAttribute()
    {
        return $this->attributes['foto_pad'] ?? $this->attributes['foto_path'] ?? null;
    }
    
    // Helper method voor veilige datum formatting
    public function formatDatum($datumVeld, $format = 'd/m/Y')
    {
        $datum = $this->{$datumVeld};
        
        if (!$datum) {
            return null;
        }
        
        // Als het al een Carbon instance is
        if ($datum instanceof \Carbon\Carbon) {
            return $datum->format($format);
        }
        
        // Als het een string is, probeer het te parsen
        if (is_string($datum)) {
            try {
                return \Carbon\Carbon::parse($datum)->format($format);
            } catch (\Exception $e) {
                return $datum; // Return original string als parsing faalt
            }
        }
        
        return $datum;
    }

    // Helper methods
    public function isOverdue()
    {
        $datum = $this->verwachte_terugbring_datum ?: $this->verwachte_retour_datum;
        
        if (!$datum) {
            return false;
        }
        
        // Zorg ervoor dat we een Carbon instance hebben
        if (is_string($datum)) {
            try {
                $datum = \Carbon\Carbon::parse($datum);
            } catch (\Exception $e) {
                return false;
            }
        }
        
        return $datum && $datum->isPast() && $this->status !== 'teruggegeven';
    }

    public function getDisplayNaam()
    {
        $onderdeelType = $this->onderdeel_type ?? 'testzadel';
        
        switch($onderdeelType) {
            case 'testzadel':
                $merk = $this->zadel_merk ?? '';
                $model = $this->zadel_model ?? '';
                return trim($merk . ' ' . $model);
            case 'nieuw zadel':
                $merk = $this->zadel_merk ?? '';
                $model = $this->zadel_model ?? '';
                return 'Nieuw zadel: ' . trim($merk . ' ' . $model);
            case 'zooltjes':
                return 'Zooltjes: ' . ($this->onderdeel_omschrijving ?: $this->zadel_merk ?: 'Onbekend');
            case 'Lake schoenen':
                return 'Lake schoenen: ' . ($this->onderdeel_omschrijving ?: $this->zadel_merk ?: 'Onbekend');
            default:
                return $this->onderdeel_omschrijving ?: $this->zadel_merk ?: 'Onbekend onderdeel';
        }
    }
    
    // Override getAttribute om veilige datum handling te garanderen
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        // Voor datum velden, zorg ervoor dat we altijd een Carbon instance teruggeven of null
        $dateFields = [
            'uitleen_datum', 'verwachte_retour_datum', 'werkelijke_retour_datum',
            'gearchiveerd_op', 'laatste_herinnering', 'herinnering_verstuurd_op'
        ];
        
        if (in_array($key, $dateFields) && $value && !($value instanceof \Carbon\Carbon)) {
            try {
                return \Carbon\Carbon::parse($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return $value;
    }
    public function getFormattedUitleenDatum()
    {
        return $this->formatDatum('uitleen_datum');
    }
    
    public function getFormattedVerwachteRetourDatum()
    {
        return $this->formatDatum('verwachte_retour_datum');
    }
    
    public function getFormattedWerkelijkeRetourDatum()
    {
        return $this->formatDatum('werkelijke_retour_datum');
    }
}