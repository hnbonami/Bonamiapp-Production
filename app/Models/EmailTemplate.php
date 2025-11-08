<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type', 
        'subject',
        'body_html', // Database kolom naam
        'description',
        'is_active',
        'organisatie_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Template types
    const TYPE_TESTZADEL_REMINDER = 'testzadel_reminder';
    const TYPE_WELCOME_CUSTOMER = 'welcome_customer';
    const TYPE_WELCOME_EMPLOYEE = 'welcome_employee';
    const TYPE_BIRTHDAY = 'birthday';

    public static function getTypes()
    {
        return [
            self::TYPE_TESTZADEL_REMINDER => 'Testzadel Herinnering',
            self::TYPE_WELCOME_CUSTOMER => 'Welkom Klant',
            self::TYPE_WELCOME_EMPLOYEE => 'Welkom Medewerker',
            self::TYPE_BIRTHDAY => 'Verjaardag',
        ];
    }

    /**
     * Render subject with variables
     */
    public function renderSubject($variables = [])
    {
        $subject = $this->subject;
        
        foreach ($variables as $key => $value) {
            $subject = str_replace('@{{' . $key . '}}', $value, $subject);
        }
        
        return $subject;
    }

    /**
     * Render body with variables
     */
    public function renderBody($variables = [])
    {
        $body = $this->body_html;
        
        foreach ($variables as $key => $value) {
            $body = str_replace('@{{' . $key . '}}', $value, $body);
        }
        
        return $body;
    }
    
    /**
     * Relatie met organisatie
     */
    public function organisatie()
    {
        return $this->belongsTo(\App\Models\Organisatie::class);
    }
    
    /**
     * Parent template relatie (voor template overerving)
     */
    public function parentTemplate()
    {
        return $this->belongsTo(EmailTemplate::class, 'parent_template_id');
    }
    
    /**
     * Child templates (organisaties die deze template overerven)
     */
    public function childTemplates()
    {
        return $this->hasMany(EmailTemplate::class, 'parent_template_id');
    }
    
    /**
     * Scope: alleen standaard Performance Pulse templates
     */
    public function scopeDefault($query)
    {
        return $query->whereNull('organisatie_id')->where('is_default', true);
    }
    
    /**
     * Scope: templates voor specifieke organisatie
     */
    public function scopeForOrganisatie($query, $organisatieId)
    {
        return $query->where('organisatie_id', $organisatieId);
    }
    
    /**
     * Scope: actieve templates voor een type
     */
    public function scopeActiveForType($query, $type)
    {
        return $query->where('type', $type)->where('is_active', true);
    }
    
    /**
     * Check of dit een standaard Performance Pulse template is
     */
    public function isDefaultTemplate(): bool
    {
        return $this->organisatie_id === null && $this->is_default === true;
    }
    
    /**
     * Check of dit een custom organisatie template is
     */
    public function isCustomTemplate(): bool
    {
        return $this->organisatie_id !== null;
    }
    
    /**
     * Haal de juiste template op met fallback logica
     * 
     * @param string $type
     * @param int|null $organisatieId
     * @return EmailTemplate|null
     */
    public static function findTemplateWithFallback(string $type, ?int $organisatieId = null): ?EmailTemplate
    {
        // Als er een organisatie is, probeer eerst custom template te vinden
        if ($organisatieId) {
            $customTemplate = static::where('type', $type)
                                   ->where('organisatie_id', $organisatieId)
                                   ->where('is_active', true)
                                   ->first();
            
            if ($customTemplate) {
                \Log::info('✅ Custom email template gevonden', [
                    'type' => $type,
                    'organisatie_id' => $organisatieId,
                    'template_id' => $customTemplate->id
                ]);
                return $customTemplate;
            }
        }
        
        // Fallback: zoek standaard Performance Pulse template
        $defaultTemplate = static::where('type', $type)
                                ->whereNull('organisatie_id')
                                ->where('is_default', true)
                                ->where('is_active', true)
                                ->first();
        
        if ($defaultTemplate) {
            \Log::info('📧 Standaard Performance Pulse template gebruikt', [
                'type' => $type,
                'template_id' => $defaultTemplate->id
            ]);
        } else {
            \Log::warning('⚠️ Geen email template gevonden', [
                'type' => $type,
                'organisatie_id' => $organisatieId
            ]);
        }
        
        return $defaultTemplate;
    }
}