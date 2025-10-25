<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Prestatie extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prestaties';

    protected $fillable = [
        'user_id',
        'organisatie_id', // ORGANISATIE ID TOEVOEGEN
        'dienst_id',
        'klant_id', // Link naar klant (optioneel)
        'klant_naam',
        'omschrijving_dienst',
        'datum_prestatie',
        'einddatum_prestatie', // Einddatum voor prestaties die meerdere dagen duren
        'bruto_prijs',
        'btw_percentage',
        'btw_bedrag',
        'netto_prijs',
        'commissie_percentage',
        'commissie_bedrag',
        'is_gefactureerd',
        'is_uitgevoerd', // Status of dienst is uitgevoerd
        'factuur_naar_klant', // Of er een factuur naar klant is gestuurd
        'factuur_nummer',
        'kwartaal',
        'jaar',
        'opmerkingen',
    ];

    protected $casts = [
        'datum_prestatie' => 'date',
        'einddatum_prestatie' => 'date', // Cast naar Carbon date object
        'bruto_prijs' => 'decimal:2',
        'btw_percentage' => 'decimal:2',
        'btw_bedrag' => 'decimal:2',
        'netto_prijs' => 'decimal:2',
        'commissie_percentage' => 'decimal:2',
        'commissie_bedrag' => 'decimal:2',
        'is_gefactureerd' => 'boolean',
        'is_uitgevoerd' => 'boolean',
        'factuur_naar_klant' => 'boolean',
    ];

    /**
     * Boot method - automatisch kwartaal en jaar bepalen
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($prestatie) {
            if (!$prestatie->kwartaal || !$prestatie->jaar) {
                $datum = Carbon::parse($prestatie->datum_prestatie);
                $prestatie->jaar = $datum->year;
                $prestatie->kwartaal = 'Q' . $datum->quarter;
            }
        });
    }

    /**
     * Coach die deze prestatie heeft geleverd
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dienst die is geleverd
     */
    public function dienst()
    {
        return $this->belongsTo(Dienst::class, 'dienst_id')->withDefault([
            'naam' => 'Andere',
            'standaard_prijs' => 0,
            'commissie_percentage' => 0
        ]);
    }

    /**
     * Klant (optioneel - voor link naar klantendatabase)
     */
    public function klant()
    {
        return $this->belongsTo(\App\Models\Klant::class)->withDefault();
    }

    /**
     * Bereken alle bedragen automatisch
     */
    public function berekenBedragen(): void
    {
        // BTW bedrag
        $this->btw_bedrag = round($this->bruto_prijs * ($this->btw_percentage / 100), 2);
        
        // Netto prijs (bruto - btw)
        $this->netto_prijs = round($this->bruto_prijs - $this->btw_bedrag, 2);
        
        // Commissie bedrag (op netto)
        $this->commissie_bedrag = round($this->netto_prijs * ($this->commissie_percentage / 100), 2);
    }

    /**
     * Scope voor specifiek kwartaal
     */
    public function scopeVoorKwartaal($query, int $jaar, string $kwartaal)
    {
        return $query->where('jaar', $jaar)->where('kwartaal', $kwartaal);
    }

    /**
     * Scope voor specifieke coach
     */
    public function scopeVoorCoach($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    /**
     * Accessor voor commissie_bedrag - berekent inkomst medewerker
     * Formule: (Prijs incl BTW / 1.21) * (100 - organisatie commissie%) / 100
     */
    protected function commissieBedrag(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Bereken inkomst: (Prijs incl BTW / 1.21) * medewerker percentage
                $prijsExclBtw = $this->bruto_prijs / 1.21; // BTW 21% aftrekken
                $medewerkerPercentage = 100 - $this->commissie_percentage; // 100% - organisatie commissie
                return ($prijsExclBtw * $medewerkerPercentage) / 100;
            }
        );
    }
}
