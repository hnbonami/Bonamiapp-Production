<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'subject',
        'body_html',
        'body_text',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Template types
    const TYPE_TESTZADEL_REMINDER = 'testzadel_reminder';
    const TYPE_GENERAL = 'general';
    const TYPE_WELCOME_CUSTOMER = 'welcome_customer';
    const TYPE_WELCOME_EMPLOYEE = 'welcome_employee';
    const TYPE_BIRTHDAY = 'birthday';
    const TYPE_BIKEFIT_CONFIRMATION = 'bikefit_confirmation';
    const TYPE_BIKEFIT_REMINDER = 'bikefit_reminder';
    const TYPE_NEWSLETTER = 'newsletter';
    const TYPE_CUSTOM = 'custom';

    public static function getTypes(): array
    {
        return [
            self::TYPE_TESTZADEL_REMINDER => 'Testzadel Herinnering',
            self::TYPE_WELCOME_CUSTOMER => 'Welkom Nieuwe Klant',
            self::TYPE_WELCOME_EMPLOYEE => 'Welkom Nieuwe Medewerker',
            self::TYPE_BIRTHDAY => 'Verjaardag',
            self::TYPE_BIKEFIT_CONFIRMATION => 'Bikefit Bevestiging',
            self::TYPE_BIKEFIT_REMINDER => 'Bikefit Herinnering',
            self::TYPE_NEWSLETTER => 'Nieuwsbrief',
            self::TYPE_CUSTOM => 'Aangepast'
        ];
    }

    public function getTypeNameAttribute(): string
    {
        return self::getTypes()[$this->type] ?? 'Onbekend';
    }

    // Scope for active templates
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope by type
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Replace template variables with actual values
    public function renderSubject(array $variables = []): string
    {
        return $this->replaceVariables($this->subject, $variables);
    }

    public function renderBody(array $variables = []): string
    {
        return $this->replaceVariables($this->body_html, $variables);
    }

    private function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace("@{{$key}}", $value, $content);
        }
        return $content;
    }
}