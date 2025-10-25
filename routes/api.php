<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Dienst;

// ...existing code...

// API route voor het ophalen van commissie percentage voor specifieke dienst en medewerker
Route::get('/commissie-percentage', function(Request $request) {
    $dienstId = $request->input('dienst_id');
    $user = auth()->user();
    
    if (!$user || !$dienstId) {
        return response()->json(['commissie_percentage' => 0]);
    }
    
    $dienst = Dienst::find($dienstId);
    
    if (!$dienst) {
        return response()->json(['commissie_percentage' => 0]);
    }
    
    // Gebruik de User model method om het correcte commissie percentage te berekenen
    $commissiePercentage = $user->getCommissiePercentageVoorDienst($dienst);
    
    return response()->json(['commissie_percentage' => $commissiePercentage]);
})->middleware('web');

// === ANALYTICS DASHBOARD ROUTES ===
Route::get('/dashboard/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'getData'])
    ->middleware('web')
    ->name('api.dashboard.analytics');

// === INSPANNINGSTEST AI ROUTES ===
Route::post('/inspanningstest/ai-complete-analysis', [App\Http\Controllers\InspanningstestController::class, 'generateCompleteAIAnalysis'])
    ->middleware('auth:sanctum')
    ->name('api.inspanningstest.ai-complete-analysis');

// ...existing routes...