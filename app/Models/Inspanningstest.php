<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspanningstest extends Model
{
    use HasFactory;
    protected $fillable = [
        'datum', // VERPLICHT veld!
        'klant_id', 'testdatum', 'testtype', 'lichaamslengte_cm', 'lichaamsgewicht_kg', 'bmi', 'hartslag_rust_bpm', 'buikomtrek_cm',
        'startwattage', 'stappen_min', 'testresultaten', 'aerobe_drempel_vermogen', 'aerobe_drempel_hartslag',
        'anaerobe_drempel_vermogen', 'anaerobe_drempel_hartslag', 'besluit_lichaamssamenstelling',
    'advies_aerobe_drempel', 'advies_anaerobe_drempel',
    'template_kind', 'user_id',
        // Trainingstatus velden
        'slaapkwaliteit', 'eetlust', 'gevoel_op_training', 'stressniveau', 'gemiddelde_trainingstatus',
        'training_dag_voor_test', 'training_2d_voor_test',
    ];
    protected $casts = [
        'testresultaten' => 'array',
        'testdatum' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
