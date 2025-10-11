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
        'description',
        'is_active'
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
}