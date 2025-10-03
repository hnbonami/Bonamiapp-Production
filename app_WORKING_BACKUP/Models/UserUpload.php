<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserUpload extends Model
{
    use HasFactory;

    protected $table = 'user_uploads';

    protected $fillable = [
        'user_id',
        'original_name',
        'path',
        'mime',
        'size',
        'compressed',
        'compressed_path',
        'compressed_size',
    ];

    protected $casts = [
        'compressed' => 'boolean',
        'size' => 'integer',
        'compressed_size' => 'integer',
    ];
}
