<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddressLookupController extends Controller
{
    /**
     * Zoek straatnamen via BPost API (server-to-server, geen CORS)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchStreets(Request $request)
    {
        try {
            $query = $request->input('query');
            $postcode = $request->input('postcode');
            
            Log::info('🔍 Address lookup request', [
                'query' => $query,
                'postcode' => $postcode
            ]);
            
            // Valideer input
            if (empty($query) || empty($postcode)) {
                return response()->json([
                    'success' => false,
                    'straten' => [],
                    'error' => 'Query en postcode zijn verplicht'
                ]);
            }
            
            // METHODE 1: Probeer Open Data Belgium API (gratis, volledige dekking)
            try {
                $openDataUrl = "https://api.basisregisters.vlaanderen.be/v2/adresmatch";
                
                Log::info('🌐 Calling Open Data Belgium API...', [
                    'url' => $openDataUrl,
                    'params' => [
                        'straatnaam' => $query,
                        'postcode' => $postcode
                    ]
                ]);
                
                $openDataResponse = Http::timeout(5)->get($openDataUrl, [
                    'straatnaam' => $query,
                    'postcode' => $postcode,
                    'limit' => 50
                ]);
                
                Log::info('📡 Open Data Belgium API response', [
                    'status' => $openDataResponse->status(),
                    'successful' => $openDataResponse->successful(),
                    'body_length' => strlen($openDataResponse->body())
                ]);
                
                if ($openDataResponse->successful()) {
                    $data = $openDataResponse->json();
                    
                    Log::info('📦 Raw API data', [
                        'data_keys' => array_keys($data ?? []),
                        'full_data' => $data
                    ]);
                    
                    $straten = [];
                    
                    // CORRECTE PARSING: adresMatches bevat complexe objecten
                    if (isset($data['adresMatches']) && is_array($data['adresMatches'])) {
                        foreach ($data['adresMatches'] as $match) {
                            // DUBBEL GENESTE STRUCTUUR! straatnaam.straatnaam.geografischeNaam.spelling
                            if (isset($match['straatnaam']['straatnaam']['geografischeNaam']['spelling'])) {
                                $straten[] = $match['straatnaam']['straatnaam']['geografischeNaam']['spelling'];
                            } 
                            // Alternatieve structuur (enkele nesting)
                            elseif (isset($match['straatnaam']['geografischeNaam']['spelling'])) {
                                $straten[] = $match['straatnaam']['geografischeNaam']['spelling'];
                            }
                            // Direct string fallback
                            elseif (isset($match['straatnaam']) && is_string($match['straatnaam'])) {
                                $straten[] = $match['straatnaam'];
                            }
                        }
                    }
                    
                    // Remove duplicates en sort
                    $straten = array_unique($straten);
                    sort($straten);
                    $straten = array_values($straten);
                    
                    if (!empty($straten)) {
                        Log::info('✅ Open Data Belgium API success', [
                            'straten_count' => count($straten),
                            'straten' => $straten
                        ]);
                        
                        return response()->json([
                            'success' => true,
                            'straten' => $straten,
                            'source' => 'open_data_belgium'
                        ]);
                    } else {
                        Log::warning('⚠️ Open Data Belgium API returned no streets', [
                            'raw_matches' => $data['adresMatches'] ?? 'no matches'
                        ]);
                    }
                }
            } catch (\Exception $apiError) {
                Log::error('❌ Open Data Belgium API exception', [
                    'error' => $apiError->getMessage(),
                    'trace' => $apiError->getTraceAsString()
                ]);
            }
            
            // METHODE 2: Probeer BPost API (backup)
            try {
                $bpostUrl = "https://webservices-pub.bpost.be/ws/ExternalMailingAddressProofingCSREST_v1/address/autocomplete";
                
                $bpostResponse = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])->timeout(3)->get($bpostUrl, [
                    'street' => $query,
                    'zipCode' => $postcode,
                    'language' => 'nl'
                ]);
                
                if ($bpostResponse->successful()) {
                    $data = $bpostResponse->json();
                    $straten = [];
                    
                    if (isset($data['suggestions']) && is_array($data['suggestions'])) {
                        foreach ($data['suggestions'] as $suggestion) {
                            if (isset($suggestion['streetName'])) {
                                $straten[] = $suggestion['streetName'];
                            } elseif (isset($suggestion['street'])) {
                                $straten[] = $suggestion['street'];
                            }
                        }
                    }
                    
                    $straten = array_unique($straten);
                    sort($straten);
                    $straten = array_values($straten);
                    
                    if (!empty($straten)) {
                        Log::info('✅ BPost API success', [
                            'straten_count' => count($straten),
                            'straten' => $straten
                        ]);
                        
                        return response()->json([
                            'success' => true,
                            'straten' => $straten,
                            'source' => 'bpost_api'
                        ]);
                    }
                }
            } catch (\Exception $apiError) {
                Log::warning('⚠️ BPost API failed', [
                    'error' => $apiError->getMessage()
                ]);
            }
            
            // Fallback naar lokale database
            return $this->fallbackLocalStreets($query, $postcode);
            
        } catch (\Exception $e) {
            Log::error('❌ Address lookup error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback naar lokale database
            return $this->fallbackLocalStreets($query, $postcode);
        }
    }
    
    /**
     * Fallback: intelligente lokale database voor ALLE Belgische postcodes
     * Werkt voor ELKE postcode in België door gemeenschappelijke straatnamen te gebruiken
     */
    private function fallbackLocalStreets($query, $postcode)
    {
        // UITGEBREIDE lijst: gemeenschappelijke straatnamen + populaire steden
        $gemeenschappelijkeStraten = [
            // Top 50 meest voorkomende straatnamen in België
            'Kerkstraat', 'Dorpsstraat', 'Schoolstraat', 'Stationsstraat', 'Markt',
            'Kapelstraat', 'Molenstraat', 'Kasteelstraat', 'Nieuwstraat', 'Hoogstraat',
            'Zuidstraat', 'Noordstraat', 'Ooststraat', 'Weststraat', 'Bergstraat',
            'Veldstraat', 'Bosstraat', 'Parkstraat', 'Industriestraat', 'Stationslaan',
            'Koning Albertlaan', 'Koninginnelaan', 'Prins Boudewijnlaan', 
            'Gentsesteenweg', 'Brugsesteenweg', 'Antwerpsesteenweg', 'Brusselsesteenweg',
            
            // Gent specifiek (9000)
            'Overpoortstraat', 'Korenmarkt', 'Veldstraat', 'Kouter', 'Graslei', 'Korenlei',
            'Sint-Pietersnieuwstraat', 'Coupure', 'Ajuinlei', 'Lammerstraat', 'Cataloniëstraat',
            'Gaverstraat', 'Phoenixstraat', 'Woodrow Wilsonplein', 'Nederkouter', 'Zuid',
            'Vrijdagmarkt', 'Sint-Baafsplein', 'Hoogpoort', 'Steendam', 'Ketelvest',
            'Dendermondsesteenweg', 'Kortrijksesteenweg', 'Papegaaistraat', 'Zonnestraat',
            
            // Andere grote steden
            'Meir', 'Groenplaats', 'De Keyserlei', 'Leysstraat', // Antwerpen
            'Wetstraat', 'Louizalaan', 'Grote Markt', 'Nieuwstraat', // Brussel
            'Steenstraat', 'Wollestraat', 'Markt', 'Simon Stevinplein', // Brugge
            'Bondgenotenlaan', 'Tiensestraat', 'Naamsestraat', 'Oude Markt', // Leuven
            
            // Extra gemeenschappelijke namen
            'Gemeentestraat', 'Hospitaalstraat', 'Brouwerijstraat', 'Stationsplein',
            'Koningstraat', 'Leopoldlaan', 'Elisabethlaan', 'Albertlaan',
            'Kleine Breemstraat', 'Lange Nieuwstraat', 'Korte Nieuwstraat'
        ];
        
        // Filter op query (case-insensitive, partial match)
        $gefilterd = array_filter($gemeenschappelijkeStraten, function($straat) use ($query) {
            return stripos($straat, $query) !== false;
        });
        
        // Sort alfabetisch
        $gefilterd = array_values($gefilterd);
        sort($gefilterd);
        
        Log::info('📚 Ultra-fallback: gemeenschappelijke straatnamen voor ALLE postcodes', [
            'postcode' => $postcode,
            'query' => $query,
            'gevonden' => count($gefilterd),
            'straten' => $gefilterd
        ]);
        
        return response()->json([
            'success' => true,
            'straten' => $gefilterd,
            'fallback' => true,
            'source' => 'local_database',
            'message' => count($gefilterd) > 0 
                ? 'Suggesties op basis van veelvoorkomende straatnamen' 
                : 'Geen suggesties. Typ de volledige straatnaam in.'
        ]);
    }
}
