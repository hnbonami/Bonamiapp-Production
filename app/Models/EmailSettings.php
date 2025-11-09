<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EmailSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisatie_id', // ⚠️ BELANGRIJK: Elke organisatie heeft eigen settings
        'company_name',
        'logo_path',
        'primary_color',
        'secondary_color',
        'email_text_color',
        'email_logo_position',
        'footer_text',
        'signature',
    ];

    /**
     * Relatie: Email settings behoren tot een organisatie
     */
    public function organisatie()
    {
        return $this->belongsTo(Organisatie::class);
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