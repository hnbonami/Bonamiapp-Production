<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToOrganisatie;

class Inspanningstest extends Model
{
    use HasFactory, BelongsToOrganisatie;

    protected $table = 'inspanningstests'; // Correcte tabelnaam zonder 'en'

    /**
     * Boot het model - zet automatisch organisatie_id bij nieuwe records
     */
    protected static function booted()
    {
        static::creating(function ($test) {
            // Zet automatisch organisatie_id als deze nog niet is gezet
            if (empty($test->organisatie_id) && auth()->check() && auth()->user()->organisatie_id) {
                $test->organisatie_id = auth()->user()->organisatie_id;
            }
        });
    }

    protected $fillable = [
        'klant_id',
        'user_id',
        'datum', // De kolom heet 'datum' niet 'testdatum' in de database!
        'testtype', 'lichaamslengte_cm', 'lichaamsgewicht_kg', 'bmi', 'hartslag_rust_bpm', 'buikomtrek_cm',
        'startwattage', 'stappen_min', 'testresultaten', 'aerobe_drempel_vermogen', 'aerobe_drempel_hartslag',
        'anaerobe_drempel_vermogen', 'anaerobe_drempel_hartslag', 'besluit_lichaamssamenstelling',
        'advies_aerobe_drempel', 'advies_anaerobe_drempel',
        'template_kind',
        // Trainingstatus velden
        'slaapkwaliteit', 'eetlust', 'gevoel_op_training', 'stressniveau', 'gemiddelde_trainingstatus',
        'training_dag_voor_test', 'training_2d_voor_test',
        // AI Analyse veld
        'complete_ai_analyse',
        // Analyse en protocol velden
        'analyse_methode', 'testlocatie', 'protocol', 'weersomstandigheden', 'specifieke_doelstellingen',
        'vetpercentage', 'maximale_hartslag_bpm', 'stappen_watt',
        // Trainingszones velden
        'trainingszones_data', // Zones data JSON
        'zones_methode',
        'zones_aantal', 
        'zones_eenheid',
        'zone_template_id', // NIEUW: Link naar gekozen zone template
    ];
    protected $casts = [
        'datum' => 'date', // Cast 'datum' kolom als date
        'testresultaten' => 'array', // Cast testresultaten als array
        'slaapkwaliteit' => 'integer',
        'eetlust' => 'integer',
        'gevoel_op_training' => 'integer',
        'stressniveau' => 'integer',
        'gemiddelde_trainingstatus' => 'decimal:1',
    ];

    // Accessor voor backwards compatibility - als code 'testdatum' gebruikt, geef dan 'datum' terug
    public function getTestdatumAttribute()
    {
        return $this->datum;
    }

    // Mutator voor backwards compatibility - als code 'testdatum' set, sla op als 'datum'
    public function setTestdatumAttribute($value)
    {
        $this->attributes['datum'] = $value;
    }

    /**
     * Relatie: Inspanningstest hoort bij een klant
     */
    public function klant()
    {
        return $this->belongsTo(Klant::class);
    }

    /**
     * Relatie: Inspanningstest hoort bij een gebruiker (tester)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relatie: Inspanningstest hoort bij een organisatie
     */
    public function organisatie()
    {
        return $this->belongsTo(Organisatie::class);
    }

    /**
     * NIEUW: Relatie naar gekozen zone template
     */
    public function zoneTemplate()
    {
        return $this->belongsTo(TrainingsZonesTemplate::class, 'zone_template_id');
    }
}
