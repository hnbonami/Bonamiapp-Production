<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstagramPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'titel',
        'caption', 
        'afbeelding',
        'hashtags',
        'status',
        'gepubliceerd_op'
    ];

    protected $casts = [
        'hashtags' => 'array',
        'gepubliceerd_op' => 'datetime'
    ];
}
