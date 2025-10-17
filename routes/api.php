// ...existing routes...

// === INSPANNINGSTEST AI ROUTES ===
Route::post('/inspanningstest/ai-complete-analysis', [App\Http\Controllers\InspanningstestController::class, 'generateCompleteAIAnalysis'])
    ->middleware('auth:sanctum')
    ->name('api.inspanningstest.ai-complete-analysis');

// ...existing routes...