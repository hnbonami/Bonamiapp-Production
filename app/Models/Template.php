<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $table = 'templates';

    protected $fillable = [
        'naam',
        'type', 
        'inhoud',
        'variabelen',
        'is_actief',
        // Also allow old column names for compatibility
        'name',
        'html_contents',
        'background_images'
    ];

    protected $casts = [
        'is_actief' => 'boolean',
        // Remove automatic JSON casting to prevent double parsing
        // 'inhoud' => 'array',
        // 'variabelen' => 'array',
    ];

    // Map old attribute names to new ones
    public function getNameAttribute()
    {
        return $this->naam;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['naam'] = $value;
    }

    public function getHtmlContentsAttribute()
    {
        return $this->inhoud;
    }

    public function setHtmlContentsAttribute($value)
    {
        $this->attributes['inhoud'] = $value;
    }

    public function getBackgroundImagesAttribute()
    {
        return $this->variabelen;
    }

    public function setBackgroundImagesAttribute($value)
    {
        $this->attributes['variabelen'] = $value;
    }
}
