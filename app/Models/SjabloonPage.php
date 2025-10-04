<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SjabloonPage extends Model
{
    use HasFactory;

    protected $table = 'sjabloon_pages';

    protected $fillable = [
        'sjabloon_id',
        'page_number',
        'content',
        'url',
        'background_image',
        'is_url_page'
    ];

    protected $casts = [
        'is_url_page' => 'boolean',
        'page_number' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function sjabloon()
    {
        return $this->belongsTo(Sjabloon::class);
    }
}