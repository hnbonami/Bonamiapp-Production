<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EmailSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisatie_id',
        'company_name',
        'logo_path',
        'email_logo_position',
        'primary_color',
        'secondary_color',
        'email_text_color',
        'footer_text',
        'signature',
    ];

    // Singleton pattern - er is maar 1 email settings record
    public static function getSettings()
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([
                'company_name' => 'Bonami Cycling',
                'primary_color' => '#667eea',
                'secondary_color' => '#764ba2',
                'footer_text' => 'Met vriendelijke groet, Het Bonami Cycling team',
                'signature' => 'Bonami Cycling - Jouw partner voor de perfecte bikefit'
            ]);
        }
        
        return $settings;
    }

    public function getLogoUrlAttribute()
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return asset('storage/' . $this->logo_path);
        }
        
        return null;
    }

    /**
     * Check if logo exists
     */
    public function hasLogo()
    {
        return !empty($this->logo_path) && \Storage::disk('public')->exists($this->logo_path);
    }
    
    /**
     * Get logo URL voor web display
     */
    public function getLogoUrl()
    {
        if ($this->hasLogo()) {
            return asset('storage/' . $this->logo_path);
        }
        return null;
    }
    
    /**
     * Get logo as base64 encoded string for email embedding
     */
    public function getLogoBase64()
    {
        if ($this->hasLogo()) {
            $path = storage_path('app/public/' . $this->logo_path);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return null;
    }

    private function getMimeType($extension)
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
        ];

        return $mimeTypes[strtolower($extension)] ?? 'image/jpeg';
    }
}