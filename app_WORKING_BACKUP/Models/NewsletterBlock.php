<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'newsletter_id', 'type', 'position', 'content', 'settings'
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }
}
