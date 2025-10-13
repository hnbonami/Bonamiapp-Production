<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateKey extends Model
{
    use HasFactory;

    /**
     * De tabel geassocieerd met het model.
     */
    protected $table = 'template_keys';

    /**
     * De attributen die massaal kunnen worden toegewezen.
     */
    protected $fillable = [
        'key',
        'description', 
        'category'
    ];

    /**
     * Accessor voor display_name (gebruikt key als display naam)
     */
    public function getDisplayNameAttribute()
    {
        return $this->description ?: $this->key;
    }

    /**
     * Accessor voor placeholder (gebruikt key als placeholder)
     */
    public function getPlaceholderAttribute()
    {
        return $this->key;
    }

    /**
     * Scope voor filtering op categorie
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope voor bikefit keys
     */
    public function scopeBikefit($query)
    {
        return $query->where('category', 'bikefit');
    }

    /**
     * Scope voor klant keys
     */
    public function scopeKlant($query)
    {
        return $query->where('category', 'klant');
    }
}