<?php

namespace App\Helpers;

use App\Models\Sjabloon;

class SjabloonHelper
{
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
     * Check if a matching sjabloon exists for given testtype
     */
    public static function hasMatchingTemplate($testtype, $category = null)
    {
        return self::findMatchingTemplate($testtype, $category) !== null;
    }

    /**
     * Get all available testtypes from sjablonen
     */
    public static function getAvailableTesttypes($category = null)
    {
        $query = Sjabloon::where('is_actief', true)->whereNotNull('testtype');
        
        if ($category) {
            $query->where('categorie', $category);
        }
        
        return $query->pluck('testtype')->unique()->sort()->values();
    }
}