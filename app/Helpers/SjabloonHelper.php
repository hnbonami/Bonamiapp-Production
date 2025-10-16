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
    public static function findMatchingTemplate($testtype, $categorie)
    {
        \Log::info('🔍 SjabloonHelper::findMatchingTemplate called', [
            'testtype' => $testtype,
            'categorie' => $categorie
        ]);
        
        // DEBUG: Log alle beschikbare sjablonen voor deze categorie
        $allSjablonen = Sjabloon::where('categorie', $categorie)->get();
        \Log::info('📋 Alle sjablonen in categorie', [
            'categorie' => $categorie,
            'count' => $allSjablonen->count(),
            'sjablonen' => $allSjablonen->map(function($s) {
                return [
                    'id' => $s->id,
                    'naam' => $s->naam,
                    'testtype' => $s->testtype ?? 'NULL'
                ];
            })->toArray()
        ]);
        
        // Eerst: exacte match proberen
        $sjabloon = Sjabloon::where('categorie', $categorie)
            ->where('testtype', $testtype)
            ->first();
            
        if ($sjabloon) {
            \Log::info('✅ Exact match found', ['sjabloon' => $sjabloon->naam]);
            return $sjabloon;
        }
        
        // NIEUW: Flexibele matching voor inspanningstesten
        if ($categorie === 'inspanningstest') {
            // Map testtype naar zoektermen
            $searchMappings = [
                'looptest' => ['lopen', 'looptest', 'loop'],
                'fietstest' => ['fietsen', 'fietstest', 'fiets'],
                'veldtest_lopen' => ['veldtest lopen', 'veldtest_lopen', 'lopen'],
                'veldtest_fietsen' => ['veldtest fietsen', 'veldtest_fietsen', 'fietsen'],
                'veldtest_zwemmen' => ['veldtest zwemmen', 'veldtest_zwemmen', 'zwemmen'],
            ];
            
            $searchTerms = $searchMappings[$testtype] ?? [$testtype];
            
            \Log::info('🔍 Trying flexible matching', [
                'testtype' => $testtype,
                'search_terms' => $searchTerms
            ]);
            
            // Zoek op basis van naam (case-insensitive partial match)
            foreach ($searchTerms as $term) {
                \Log::info('  🔎 Searching for term', ['term' => $term]);
                
                $sjabloon = Sjabloon::where('categorie', $categorie)
                    ->where('naam', 'LIKE', '%' . $term . '%')
                    ->first();
                    
                if ($sjabloon) {
                    \Log::info('✅ Flexible match found', [
                        'search_term' => $term,
                        'sjabloon' => $sjabloon->naam
                    ]);
                    return $sjabloon;
                } else {
                    \Log::info('  ❌ No match for term', ['term' => $term]);
                }
            }
        }
        
        \Log::warning('❌ No matching template found after all attempts', [
            'testtype' => $testtype,
            'categorie' => $categorie
        ]);
        
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