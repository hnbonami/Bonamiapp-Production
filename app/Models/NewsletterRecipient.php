<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'newsletter_id', 'type', 'recipient_id', 'email', 'name', 'segment', 'status', 'sent_at', 'error'
    ];

    protected $dates = ['sent_at'];

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }
}
