<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BikefitImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'bikefit_id', 'path', 'caption', 'position', 'is_cover'
    ];

    protected $casts = [
        'is_cover' => 'boolean',
    ];

    public function bikefit()
    {
        return $this->belongsTo(Bikefit::class);
    }
}
