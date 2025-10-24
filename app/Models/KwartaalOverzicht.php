<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KwartaalOverzicht extends Model
{
    use HasFactory;

    protected $table = 'kwartaal_overzichten';

    protected $fillable = [
        'user_id',
        'jaar',
        'kwartaal',
        'totaal_bruto',
        'totaal_btw',
        'totaal_netto',
        'totaal_commissie',
        'aantal_prestaties',
        'is_afgesloten',
        'afgesloten_op',
    ];

    protected $casts = [
        'totaal_bruto' => 'decimal:2',
        'totaal_btw' => 'decimal:2',
        'totaal_netto' => 'decimal:2',
        'totaal_commissie' => 'decimal:2',
        'is_afgesloten' => 'boolean',
        'afgesloten_op' => 'date',
    ];

    /**
     * Coach van dit overzicht
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Prestaties in dit kwartaal
     */
    public function prestaties()
    {
        return Prestatie::where('user_id', $this->user_id)
                       ->where('jaar', $this->jaar)
                       ->where('kwartaal', $this->kwartaal);
    }

    /**
     * Herbereken totalen op basis van prestaties
     */
    public function herbereken(): void
    {
        $prestaties = $this->prestaties()->get();

        $this->totaal_bruto = $prestaties->sum('bruto_prijs');
        $this->totaal_btw = $prestaties->sum('btw_bedrag');
        $this->totaal_netto = $prestaties->sum('netto_prijs');
        $this->totaal_commissie = $prestaties->sum('commissie_bedrag');
        $this->aantal_prestaties = $prestaties->count();
        
        $this->save();
    }
}
