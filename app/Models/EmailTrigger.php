<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTrigger extends Model
{
    use HasFactory;

    protected $fillable = [
        'trigger_name',
        'template_id', 
        'recipient_email',
        'variables',
        'sent_at',
        'status'
    ];
    
    protected $casts = [
        'sent_at' => 'datetime',
        'variables' => 'array'
    ];
    
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }
}