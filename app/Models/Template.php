<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $table = 'templates';

    protected $fillable = [
        'name',
        'type',
        'description',
        'content',
        // Also allow old column names for compatibility
        'naam',
        'inhoud',
        'variabelen',
        'is_actief',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
