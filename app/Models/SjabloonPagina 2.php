<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SjabloonPagina extends Model
{
    use HasFactory;

    protected $table = 'sjabloon_paginas';

    protected $fillable = [
        'sjabloon_id',
        'pagina_nummer',
        'achtergrond_url',
        'inhoud'
    ];

    protected $casts = [
        'pagina_nummer' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sjabloon()
    {
        return $this->belongsTo(Sjabloon::class);
    }
}