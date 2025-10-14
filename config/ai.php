<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Analysis Configuration
    |--------------------------------------------------------------------------
    |
    | Configuratie voor AI-gestuurde analyse van inspanningstesten
    | Gebruikt OpenAI GPT models voor sportmedische adviezen
    |
    */

    // OpenAI API configuratie
    'openai_key' => env('OPENAI_API_KEY'),
    
    // Model selectie - gpt-4o-mini is kostenefficiënt en snel
    'model' => env('AI_MODEL', 'gpt-4o-mini'),
    
    // Maximum tokens per response (verhoogd voor zeer uitgebreide complete analyses)
    'max_tokens' => env('AI_MAX_TOKENS', 1500),
    
    // Temperature voor consistentie (0.4 = balans tussen creativiteit en feitelijkheid)
    'temperature' => env('AI_TEMPERATURE', 0.4),
    
    // Timeout voor API calls (seconden)
    'timeout' => env('AI_TIMEOUT', 30),
    
    // Feature flags
    'enabled' => env('AI_ANALYSIS_ENABLED', true),
    'fallback_enabled' => env('AI_FALLBACK_ENABLED', true),
    
    // Logging
    'log_requests' => env('AI_LOG_REQUESTS', true),
    'log_responses' => env('AI_LOG_RESPONSES', false), // Privacy!
];