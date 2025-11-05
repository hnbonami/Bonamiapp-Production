<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\BelongsToOrganisatie;

class Bikefit extends Model
{
    use HasFactory, BelongsToOrganisatie;

    protected $casts = [
        'datum' => 'date',
        'image_urls' => 'array',
        'zadel_lengte_center_top' => 'float',
    ];

    protected $fillable = [
        'user_id',
        'bike_id',
        'name',
        'date',
        'notes',
        'measurement_type',
        'saddle_height',
        'saddle_setback',
        'saddle_angle',
        'handlebar_reach',
        'handlebar_drop',
        'handlebar_width',
        'crank_length',
        'stem_length',
        'stem_angle',
        'saddle_to_bar_vertical',
        'saddle_to_bar_horizontal',
        'stack',
        'reach',
        
        // Custom result kolommen voor verschillende contexten
        'prognose_zadelhoogte',
        'prognose_zadelterugstand',
        'prognose_zadelterugstand_top',
        'prognose_horizontale_reach',
        'prognose_reach',
        'prognose_drop',
        'prognose_cranklengte',
        'prognose_stuurbreedte',
        
        'voor_zadelhoogte',
        'voor_zadelterugstand',
        'voor_zadelterugstand_top',
        'voor_horizontale_reach',
        'voor_reach',
        'voor_drop',
        'voor_cranklengte',
        'voor_stuurbreedte',
        
        'na_zadelhoogte',
        'na_zadelterugstand',
        'na_zadelterugstand_top',
        'na_horizontale_reach',
        'na_reach',
        'na_drop',
        'na_cranklengte',
        'na_stuurbreedte',
    ];

    public function images()
    {
        return $this->hasMany(BikefitImage::class)->orderBy('position');
    }

    public function testzadelStatus()
    {
        return $this->hasOne(TestzadelStatus::class);
    }

    public function klant()
    {
        return $this->belongsTo(Klant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }

    public function testzadels()
    {
        return $this->hasMany(Testzadel::class);
    }

    /**
     * Get the custom results for this bikefit.
     */
    public function customResults()
    {
        return $this->hasMany(BikefitCustomResult::class);
    }

    /**
     * Get custom results for a specific context.
     */
    public function customResultsForContext($context)
    {
        return $this->customResults()->forContext($context);
    }

    // Event listener voor automatische testzadel aanmaak
    protected static function booted()
    {
        static::saved(function ($bikefit) {
            // Als nieuw_testzadel is ingevuld, maak automatisch een testzadel aan
            if (!empty($bikefit->nieuw_testzadel) && !empty($bikefit->type_zadel) && !empty($bikefit->zadelbreedte)) {
                // Check of er al een testzadel bestaat voor deze bikefit
                $bestaandeTestzadel = \App\Models\Testzadel::where('bikefit_id', $bikefit->id)->first();
                
                if (!$bestaandeTestzadel) {
                    $merkModel = explode(' ', trim($bikefit->nieuw_testzadel), 2);
                    $merk = $merkModel[0] ?? 'Onbekend';
                    $model = $merkModel[1] ?? '';
                    
                    \App\Models\Testzadel::create([
                        'klant_id' => $bikefit->klant_id,
                        'bikefit_id' => $bikefit->id,
                        'zadel_merk' => $merk,
                        'zadel_model' => $model,
                        'zadel_type' => $bikefit->type_zadel,
                        'zadel_breedte' => (int) $bikefit->zadelbreedte,
                        'uitleen_datum' => now()->toDateString(),
                        'verwachte_retour_datum' => now()->addWeeks(2)->toDateString(),
                        'status' => 'uitgeleend',
                        'zadel_beschrijving' => 'Automatisch aangemaakt vanuit bikefit',
                        'opmerkingen' => 'Testzadel automatisch toegewezen na bikefit op ' . $bikefit->datum->format('d/m/Y'),
                        'gearchiveerd' => false
                    ]);
                }
            }
        });

        static::creating(function ($bikefit) {
            // Zet automatisch organisatie_id als deze nog niet is gezet
            if (empty($bikefit->organisatie_id) && auth()->check() && auth()->user()->organisatie_id) {
                $bikefit->organisatie_id = auth()->user()->organisatie_id;
            }
        });
    }
}
