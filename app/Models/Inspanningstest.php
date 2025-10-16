<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspanningstest extends Model
{
    use HasFactory;
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Relatie met Klant
     */
    public function klant()
    {
        return $this->belongsTo(Klant::class, 'klant_id');
    }
}
