<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Toon rollen beheer pagina met feature toegang per rol
     * Admin ziet alleen features van zijn organisatie
     * Superadmin ziet alle features
     */
    public function roles()
    {
        // Bereken statistieken per rol
        $roleStats = [
            'total' => User::count(),
            'superadmin' => User::where('role', 'superadmin')->count(),
            'admin' => User::where('role', 'admin')->count(),
            'medewerker' => User::where('role', 'medewerker')->count(),
            'klant' => User::where('role', 'klant')->count(),
        ];

        // Definieer beschikbare rollen met info
        $roles = [
            [
                'key' => 'admin',
                'name' => 'Administrator',
                'description' => 'Volledige toegang tot alle functionaliteiten',
                'permissions' => 'Alle rechten',
                'color' => 'purple'
            ],
            [
                'key' => 'medewerker',
                'name' => 'Medewerker',
                'description' => 'Toegang tot klantenbeheer en testen',
                'permissions' => 'Bikefit, Inspanningstests, Klanten',
                'color' => 'orange'
            ],
            [
                'key' => 'klant',
                'name' => 'Klant',
                'description' => 'Beperkte toegang tot eigen gegevens',
                'permissions' => 'Alleen eigen profiel',
                'color' => 'cyan'
            ]
        ];
        
        // Haal features op (gefilterd voor admins)
        $features = $this->getFeaturesForCurrentUser();
        
        // Haal role-feature mappings op
        $roleFeatures = $this->getRoleFeatureMapping();
        
        // Bereken totaal features voor statistiek
        $totalFeatures = $features->count();

        return view('admin.users.roles', compact('roleStats', 'roles', 'features', 'roleFeatures', 'totalFeatures'));
    }
    
    /**
     * Toggle feature voor specifieke rol
     * Valideer dat de feature bij de organisatie hoort (voor admins)
     */
    public function toggleRoleFeature(Request $request, string $roleKey, int $featureId)
    {
        try {
            // Valideer input
            $validated = $request->validate([
                'is_active' => 'required|boolean'
            ]);
            
            // Check of feature bestaat
            $feature = \App\Models\Feature::findOrFail($featureId);
            
            // BELANGRIJK: Voor admins - check of hun organisatie deze feature heeft
            if (auth()->user()->role !== 'superadmin') {
                $organisatie = auth()->user()->organisatie;
                
                if (!$organisatie) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Geen organisatie gevonden'
                    ], 403);
                }
                
                // Check of organisatie deze feature heeft geactiveerd
                $hasFeature = $organisatie->features()
                    ->where('feature_id', $featureId)
                    ->wherePivot('is_actief', true)
                    ->exists();
                
                if (!$hasFeature) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Deze feature is niet beschikbaar voor uw organisatie'
                    ], 403);
                }
            }
            
            // Check of rol geldig is
            $validRoles = ['admin', 'medewerker', 'klant'];
            if (!in_array($roleKey, $validRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ongeldige rol'
                ], 400);
            }
            
            // Toggle de feature voor deze rol
            if ($validated['is_active']) {
                // Activeer feature voor rol
                \DB::table('feature_role')->updateOrInsert(
                    [
                        'role_key' => $roleKey,
                        'feature_id' => $featureId
                    ],
                    [
                        'is_actief' => true,
                        'updated_at' => now()
                    ]
                );
                
                \Log::info("Feature {$feature->naam} geactiveerd voor rol {$roleKey} door " . auth()->user()->name);
            } else {
                // Deactiveer feature voor rol
                \DB::table('feature_role')
                    ->where('role_key', $roleKey)
                    ->where('feature_id', $featureId)
                    ->update([
                        'is_actief' => false,
                        'updated_at' => now()
                    ]);
                
                \Log::info("Feature {$feature->naam} gedeactiveerd voor rol {$roleKey} door " . auth()->user()->name);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Feature succesvol bijgewerkt'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Fout bij togglen role feature: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Er ging iets mis: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Haal features op voor huidige gebruiker
     * Admin: alleen features van zijn organisatie
     * Superadmin: alle features
     */
    private function getFeaturesForCurrentUser()
    {
        $user = auth()->user();
        
        // Superadmin ziet alle features
        if ($user->role === 'superadmin') {
            return \App\Models\Feature::orderBy('categorie')
                ->orderBy('sorteer_volgorde')
                ->get();
        }
        
        // Admin ziet alleen features van zijn organisatie die actief zijn
        $organisatie = $user->organisatie;
        
        if (!$organisatie) {
            return collect([]);
        }
        
        return $organisatie->features()
            ->wherePivot('is_actief', true)
            ->orderBy('categorie')
            ->orderBy('sorteer_volgorde')
            ->get();
    }
    
    /**
     * Haal role-feature mapping op
     * Geeft terug welke features per rol actief zijn
     * Gefilterd op organisatie features voor admins
     */
    private function getRoleFeatureMapping()
    {
        $user = auth()->user();
        $roleFeatures = [
            'admin' => [],
            'medewerker' => [],
            'klant' => []
        ];
        
        // Haal beschikbare features op (gefilterd voor admins)
        $availableFeatures = $this->getFeaturesForCurrentUser()->pluck('id');
        
        // Haal alle actieve role-feature koppelingen op
        $mappings = \DB::table('feature_role')
            ->where('is_actief', true)
            ->whereIn('feature_id', $availableFeatures)
            ->get();
        
        foreach ($mappings as $mapping) {
            if (isset($roleFeatures[$mapping->role_key])) {
                $roleFeatures[$mapping->role_key][] = $mapping->feature_id;
            }
        }
        
        return $roleFeatures;
    }
}