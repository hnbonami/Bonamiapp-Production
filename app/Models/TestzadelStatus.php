<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestzadelStatus extends Model
{
    protected $table = 'testzadel_status';
    
    protected $fillable = [
        'bikefit_id',
        'status',
        'notitie'
    ];

    public function bikefit()
    {
        return $this->belongsTo(Bikefit::class);
    }
}