<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bikefit extends Model
{
    use HasFactory;

    protected $casts = [
        'datum' => 'date',
        'image_urls' => 'array',
        'zadel_lengte_center_top' => 'float',
    ];

    protected $fillable = [
        'klant_id',
        'datum',
        'testtype',
        'template_kind',
        // info fiets
        'fietsmerk', 'kadermaat', 'bouwjaar', 'frametype',
        // voetmeting
        'schoenmaat', 'voetbreedte', 'voetpositie',
        'lengte_cm', 'binnenbeenlengte_cm', 'armlengte_cm', 'romplengte_cm', 'schouderbreedte_cm',
        'zadel_trapas_hoek', 'zadel_trapas_afstand', 'stuur_trapas_hoek', 'stuur_trapas_afstand',
        'zadel_lengte_center_top',
        'aanpassingen_zadel', 'aanpassingen_setback', 'aanpassingen_reach', 'aanpassingen_drop',
        'type_zadel', 'zadeltil', 'zadelbreedte', 'nieuw_testzadel',
        'rotatie_aanpassingen', 'inclinatie_aanpassingen', 'ophoging_li', 'ophoging_re',
        'opmerkingen', 'interne_opmerkingen',
        // anamnese
        'algemene_klachten', 'beenlengteverschil', 'beenlengteverschil_cm', 'lenigheid_hamstrings', 'steunzolen', 'steunzolen_reden',
        // functionele mobiliteit
        'straight_leg_raise_links', 'straight_leg_raise_rechts',
        'knieflexie_links', 'knieflexie_rechts',
        'heup_endorotatie_links', 'heup_endorotatie_rechts',
        'heup_exorotatie_links', 'heup_exorotatie_rechts',
        'enkeldorsiflexie_links', 'enkeldorsiflexie_rechts',
        'one_leg_squat_links', 'one_leg_squat_rechts',
        // aanpassingen stuurpen
        'aanpassingen_stuurpen_aan', 'aanpassingen_stuurpen_pre', 'aanpassingen_stuurpen_post',
        // type fitting
        'type_fitting',
        // images via relation
        // note: image URLs can also be stored transiently in 'image_urls' if needed
        'image_urls',
        'user_id',
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
    }
}
