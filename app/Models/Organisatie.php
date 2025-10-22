<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisatie extends Model
{
    use HasFactory;

    protected $table = 'organisaties';

    protected $fillable = [
        'naam',
        'email',
        'telefoon',
        'adres',
        'postcode',
        'plaats',
        'btw_nummer',
        'logo_path',
        'status',
        'trial_eindigt_op',
        'maandelijkse_prijs',
        'notities',
    ];

    protected $casts = [
        'trial_eindigt_op' => 'date',
        'maandelijkse_prijs' => 'decimal:2',
    ];

    /**
     * Relatie: organisatie heeft meerdere gebruikers
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'organisatie_id');
    }

    /**
     * Relatie: organisatie heeft meerdere klanten
     */
    public function klanten(): HasMany
    {
        return $this->hasMany(Klant::class, 'organisatie_id');
    }

    /**
     * Check of organisatie actief is
     */
    public function isActief(): bool
    {
        return $this->status === 'actief';
    }

    /**
     * Check of trial verlopen is
     */
    public function isTrialVerlopen(): bool
    {
        if ($this->status !== 'trial' || !$this->trial_eindigt_op) {
            return false;
        }

        return $this->trial_eindigt_op->isPast();
    }

    /**
     * Scope: alleen actieve organisaties
     */
    public function scopeActief($query)
    {
        return $query->where('status', 'actief');
    }
}
