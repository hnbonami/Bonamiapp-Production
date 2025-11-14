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
                
                $openDataResponse = Http::timeout(3)->get($openDataUrl, [
                    'straatnaam' => $query,
                    'postcode' => $postcode,
                    'limit' => 20
                ]);
                
                if ($openDataResponse->successful()) {
                    $data = $openDataResponse->json();
                    $straten = [];
                    
                    if (isset($data['adresMatches']) && is_array($data['adresMatches'])) {
                        foreach ($data['adresMatches'] as $match) {
                            if (isset($match['straatnaam'])) {
                                $straten[] = $match['straatnaam'];
                            }
                        }
                    }
                    
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
                    }
                }
            } catch (\Exception $apiError) {
                Log::warning('⚠️ Open Data Belgium API failed', [
                    'error' => $apiError->getMessage()
                ]);
            }
            
            // METHODE 2: Probeer GeoNames API (backup, wereldwijde dekking)
            try {
                $geoNamesUrl = "http://api.geonames.org/postalCodeSearchJSON";
                
                $geoNamesResponse = Http::timeout(3)->get($geoNamesUrl, [
                    'postalcode' => $postcode,
                    'country' => 'BE',
                    'username' => 'demo', // Vervang door eigen GeoNames username
                    'maxRows' => 50
                ]);
                
                if ($geoNamesResponse->successful()) {
                    $data = $geoNamesResponse->json();
                    $straten = [];
                    
                    // GeoNames geeft geen straatnamen, maar wel plaatsnamen
                    // We gebruiken dit als ultra-fallback
                    
                    Log::info('✅ GeoNames API responded', [
                        'data' => $data
                    ]);
                }
            } catch (\Exception $apiError) {
                Log::warning('⚠️ GeoNames API failed', [
                    'error' => $apiError->getMessage()
                ]);
            }
            
            // METHODE 3: Probeer BPost API (origineel)
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
        // Gemeenschappelijke straatnamen die in bijna ELKE Belgische gemeente voorkomen
        $gemeenschappelijkeStraten = [
            'Kerkstraat', 'Dorpsstraat', 'Schoolstraat', 'Stationsstraat', 'Markt',
            'Kapelstraat', 'Molenstraat', 'Kasteelstraat', 'Nieuwstraat', 'Hoogstraat',
            'Zuidstraat', 'Noordstraat', 'Ooststraat', 'Weststraat', 'Bergstraat',
            'Veldstraat', 'Bosstraat', 'Parkstraat', 'Industriestraat', 'Stationslaan',
            'Koning Albertlaan', 'Koninginnelaan', 'Prins Boudewijnlaan', 'Gentsesteenweg',
            'Brugsesteenweg', 'Antwerpsesteenweg', 'Brusselsesteenweg', 'Grote Markt'
        ];
        
        // Filter op query (case-insensitive)
        $gefilterd = array_filter($gemeenschappelijkeStraten, function($straat) use ($query) {
            return stripos($straat, $query) !== false;
        });
        
        $gefilterd = array_values($gefilterd);
        
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
            'message' => count($gefilterd) > 0 
                ? 'Suggesties op basis van veelvoorkomende straatnamen' 
                : 'Geen suggesties. Typ de volledige straatnaam in.'
        ]);
    }
}
