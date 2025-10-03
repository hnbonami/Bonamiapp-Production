<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Upload extends Model
{
    protected $table = 'uploads';
    protected $fillable = [
        'original_name',
        'mime_type',
        'size',
        'path',
        'disk',
        'user_id',
        'metadata',
        'bikefit_id', // Toegevoegd zodat bikefit_id opgeslagen wordt
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function getFullPathAttribute()
    {
        return $this->path ? Storage::disk($this->disk ?? 'private')->path($this->path) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
