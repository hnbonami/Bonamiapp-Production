<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddressLookupController extends Controller
{
    /**
     * Zoek Belgische straatnamen via BPost API
     * Dit is een proxy endpoint om CORS issues te vermijden
     */
    public function searchStreets(Request $request)
    {
        $query = $request->input('query');
        $postcode = $request->input('postcode');
        
        // Valideer input
        if (!$query || strlen($query) < 2) {
            return response()->json(['straten' => []], 400);
        }
        
        if (!$postcode || strlen($postcode) !== 4) {
            return response()->json(['straten' => []], 400);
        }
        
        try {
            Log::info('🔍 BPost API lookup via backend', [
                'query' => $query,
                'postcode' => $postcode
            ]);
            
            // Call BPost API via Laravel HTTP client
            $response = Http::timeout(5)
                ->get('https://webservices-pub.bpost.be/ws/ExternalMailingAddressProofingCSREST_v1/address/autocomplete', [
                    'streetName' => $query,
                    'postalCode' => $postcode
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $straten = [];
                
                // Extract straatnamen uit response
                if (isset($data['AddressProofingResponse']['ValidatedAddresses'])) {
                    foreach ($data['AddressProofingResponse']['ValidatedAddresses'] as $address) {
                        if (isset($address['StreetName']) && !in_array($address['StreetName'], $straten)) {
                            $straten[] = $address['StreetName'];
                        }
                    }
                }
                
                Log::info('✅ Gevonden straten via BPost API', [
                    'count' => count($straten),
                    'straten' => $straten
                ]);
                
                return response()->json([
                    'success' => true,
                    'straten' => $straten
                ]);
            }
            
            // API error - return empty result
            Log::warning('⚠️ BPost API error', [
                'status' => $response->status()
            ]);
            
            return response()->json([
                'success' => false,
                'straten' => []
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ BPost API exception', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'straten' => [],
                'error' => 'API niet beschikbaar'
            ], 500);
        }
    }
}
