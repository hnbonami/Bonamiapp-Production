<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BikefitCustomResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'bikefit_id',
        'context',
        'field_name',
        'custom_value',
        'original_value',
    ];

    protected $casts = [
        'custom_value' => 'decimal:2',
        'original_value' => 'decimal:2',
    ];

    /**
     * Get the bikefit that owns this custom result.
     */
    public function bikefit()
    {
        return $this->belongsTo(Bikefit::class);
    }

    /**
     * Scope a query to only include results for a specific context.
     */
    public function scopeForContext($query, $context)
    {
        return $query->where('context', $context);
    }

    /**
     * Scope a query to only include results for a specific bikefit and context.
     */
    public function scopeForBikefitAndContext($query, $bikefitId, $context)
    {
        return $query->where('bikefit_id', $bikefitId)->where('context', $context);
    }
}