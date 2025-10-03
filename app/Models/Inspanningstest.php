<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inspanningstest extends Model
{
    use HasFactory;
    protected $fillable = [
        'klant_id', 'testdatum', 'testtype', 'lichaamslengte_cm', 'lichaamsgewicht_kg', 'bmi', 'hartslag_rust_bpm', 'buikomtrek_cm',
        'startwattage', 'stappen_min', 'testresultaten', 'aerobe_drempel_vermogen', 'aerobe_drempel_hartslag',
        'anaerobe_drempel_vermogen', 'anaerobe_drempel_hartslag', 'besluit_lichaamssamenstelling',
    'advies_aerobe_drempel', 'advies_anaerobe_drempel',
    'template_kind', 'user_id',
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
