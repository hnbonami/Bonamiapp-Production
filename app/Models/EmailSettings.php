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

    public function hasLogo()
    {
        return $this->logo_path && Storage::disk('public')->exists($this->logo_path);
    }

    // Get logo as base64 for email embedding
    public function getLogoBase64()
    {
        if (!$this->hasLogo()) {
            return null;
        }

        try {
            $logoContent = Storage::disk('public')->get($this->logo_path);
            $extension = pathinfo($this->logo_path, PATHINFO_EXTENSION);
            $mimeType = $this->getMimeType($extension);
            
            return 'data:' . $mimeType . ';base64,' . base64_encode($logoContent);
        } catch (\Exception $e) {
            \Log::error('Logo base64 error: ' . $e->getMessage());
            return null;
        }
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