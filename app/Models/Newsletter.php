<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'subject', 'from_name', 'from_email', 'status', 'created_by'
    ];

    public function blocks()
    {
        return $this->hasMany(NewsletterBlock::class)->orderBy('position');
    }

    public function recipients()
    {
        return $this->hasMany(NewsletterRecipient::class);
    }
}
