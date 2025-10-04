<?php

namespace App\Helpers;

use App\Models\Sjabloon;

class SjabloonHelper
{
    /**
     * Check if there's a matching template for given testtype and category
     */
    public static function hasMatchingTemplate($testtype, $category = null)
    {
        return self::findMatchingTemplate($testtype, $category) !== null;
    }

    /**
     * Find matching sjabloon based on testtype and category
     */
    public static function findMatchingTemplate($testtype, $category = null)
    {
        $query = Sjabloon::where('is_actief', true);
        
        // First try to match both testtype and category
        if ($testtype && $category) {
            $template = $query->where('testtype', $testtype)
                             ->where('categorie', $category)
                             ->first();
            if ($template) {
                return $template;
            }
        }
        
        // If no exact match, try just testtype
        if ($testtype) {
            $template = $query->where('testtype', $testtype)->first();
            if ($template) {
                return $template;
            }
        }
        
        // If still no match, try just category
        if ($category) {
            $template = $query->where('categorie', $category)->first();
            if ($template) {
                return $template;
            }
        }
        
        return null;
    }

    /**
     * Get all available testtypes
     */
    public static function getAvailableTesttypes()
    {
        return Sjabloon::where('is_actief', true)
                      ->whereNotNull('testtype')
                      ->pluck('testtype')
                      ->unique()
                      ->values();
    }
}