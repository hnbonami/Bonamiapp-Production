<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KlantDocument extends Model
{
    use HasFactory;

    protected $table = 'klant_documenten';

    protected $fillable = [
        'klant_id',
        'titel',
        'beschrijving',
        'bestandsnaam',
        'opgeslagen_naam',
        'bestandstype',
        'bestandsgrootte',
        'categorie',
        'upload_datum',
        'toegang', // Toegangsrechten veld
    ];

    protected $casts = [
        'upload_datum' => 'datetime',
        'gecomprimeerd' => 'boolean',
        'bestandsgrootte' => 'integer',
        'originele_grootte' => 'integer',
    ];

    // Toegangsrecht constanten
    const TOEGANG_ALLEEN_MEZELF = 'alleen_mezelf';
    const TOEGANG_KLANT = 'klant';
    const TOEGANG_ALLE_MEDEWERKERS = 'alle_medewerkers';
    const TOEGANG_IEDEREEN = 'iedereen';

    /**
     * Relatie met klant
     */
    public function klant(): BelongsTo
    {
        return $this->belongsTo(Klant::class, 'klant_id');
    }

    /**
     * Get het volledige pad naar het bestand
     */
    public function getBestandspadAttribute(): string
    {
        return storage_path('app/private/klant_documenten/' . $this->opgeslagen_naam);
    }

    /**
     * Get de bestandsgrootte in leesbare format (MB, KB)
     */
    public function getLeesbaareGrootteAttribute(): string
    {
        $bytes = $this->bestandsgrootte;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get compressie ratio als percentage
     */
    public function getCompressieRatioAttribute(): ?float
    {
        if (!$this->gecomprimeerd || !$this->originele_grootte) {
            return null;
        }
        
        return round((1 - ($this->bestandsgrootte / $this->originele_grootte)) * 100, 1);
    }

    /**
     * Check of bestand een afbeelding is
     */
    public function isAfbeelding(): bool
    {
        return in_array(strtolower($this->bestandstype), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Check of bestand een video is
     */
    public function isVideo(): bool
    {
        return in_array(strtolower($this->bestandstype), ['mp4', 'mov', 'avi', 'wmv', 'mkv']);
    }

    /**
     * Check of bestand een PDF is
     */
    public function isPdf(): bool
    {
        return strtolower($this->bestandstype) === 'pdf';
    }

    /**
     * Get icoon voor bestandstype
     */
    public function getIconAttribute(): string
    {
        if ($this->isAfbeelding()) {
            return '🖼️';
        } elseif ($this->isVideo()) {
            return '🎥';
        } elseif ($this->isPdf()) {
            return '📄';
        } elseif (in_array(strtolower($this->bestandstype), ['doc', 'docx'])) {
            return '📝';
        } elseif (in_array(strtolower($this->bestandstype), ['xls', 'xlsx'])) {
            return '📊';
        } else {
            return '📁';
        }
    }

    /**
     * Check of gebruiker toegang heeft tot dit document
     */
    public function heeftToegang($user): bool
    {
        if (!$user) {
            return false;
        }

        // Admin heeft altijd toegang
        if ($user->role === 'admin' || $user->isBeheerder()) {
            return true;
        }

        // Check toegangsrechten
        switch ($this->toegang) {
            case self::TOEGANG_ALLEEN_MEZELF:
                return $this->uploaded_by === $user->id;
            
            case self::TOEGANG_KLANT:
                return $this->uploaded_by === $user->id || 
                       ($user->role === 'klant' && $this->klant_id === $user->klant_id);
            
            case self::TOEGANG_ALLE_MEDEWERKERS:
                return $user->isMedewerker() || $user->isBeheerder();
            
            case self::TOEGANG_IEDEREEN:
                return true;
            
            default:
                return false;
        }
    }

    /**
     * Relatie met de gebruiker die het document heeft geüpload
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
