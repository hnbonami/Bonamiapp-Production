<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Upload extends Model
{
    protected $table = 'uploads';
    protected $fillable = [
        'original_name',
        'mime_type',
        'size',
        'path',
        'disk',
        'user_id',
        'metadata',
        'bikefit_id', // Toegevoegd zodat bikefit_id opgeslagen wordt
        'klant_id',
        'inspanningstest_id',
        'toegang',
        'naam',
        'beschrijving',
        'is_cover',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    // Toegangsrecht constanten
    const TOEGANG_ALLEEN_MEZELF = 'alleen_mezelf';
    const TOEGANG_KLANT = 'klant';
    const TOEGANG_ALLE_MEDEWERKERS = 'alle_medewerkers';
    const TOEGANG_IEDEREEN = 'iedereen';

    /**
     * Haal alle mogelijke toegangsrechten op
     */
    public static function getToegangsOpties(): array
    {
        return [
            self::TOEGANG_ALLEEN_MEZELF => 'Alleen mezelf',
            self::TOEGANG_KLANT => 'Klant + mezelf',
            self::TOEGANG_ALLE_MEDEWERKERS => 'Alle medewerkers',
            self::TOEGANG_IEDEREEN => 'Iedereen'
        ];
    }

    /**
     * Check of gebruiker toegang heeft tot dit document
     */
    public function heeftToegang($user): bool
    {
        // Geen gebruiker = geen toegang
        if (!$user) {
            return false;
        }

        // Admin heeft altijd toegang
        if ($user->role === 'admin') {
            return true;
        }

        // Check toegangsrechten
        switch ($this->toegang) {
            case self::TOEGANG_ALLEEN_MEZELF:
                return $this->user_id === $user->id;
            
            case self::TOEGANG_KLANT:
                // Uploader of gekoppelde klant
                return $this->user_id === $user->id || 
                       ($user->role === 'klant' && $this->klant_id === $user->klant_id);
            
            case self::TOEGANG_ALLE_MEDEWERKERS:
                // Medewerkers, admins of uploader
                return in_array($user->role, ['admin', 'medewerker']) || 
                       $this->user_id === $user->id;
            
            case self::TOEGANG_IEDEREEN:
                return true;
            
            default:
                // Standaard alleen uploader
                return $this->user_id === $user->id;
        }
    }

    public function getFullPathAttribute()
    {
        return $this->path ? Storage::disk($this->disk ?? 'private')->path($this->path) : null;
    }

    public function klant()
    {
        return $this->belongsTo(Klant::class);
    }

    public function bikefit()
    {
        return $this->belongsTo(Bikefit::class);
    }

    public function inspanningstest()
    {
        return $this->belongsTo(Inspanningstest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
