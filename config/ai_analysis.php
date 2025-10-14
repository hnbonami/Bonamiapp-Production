<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Model Configuration
    |--------------------------------------------------------------------------
    |
    | Configuratie voor het AI model dat inspanningstesten analyseert.
    | Pas deze waarden aan om het model te trainen/verbeteren.
    |
    */

    'model' => env('AI_MODEL', 'gpt-4o-mini'),
    
    'temperature' => env('AI_TEMPERATURE', 0.4),
    
    'max_tokens' => env('AI_MAX_TOKENS', 1500),
    
    /*
    |--------------------------------------------------------------------------
    | System Prompts
    |--------------------------------------------------------------------------
    |
    | Deze prompts definiëren de rol en expertise van de AI.
    |
    */

    'system_prompt' => "Je bent een ervaren sportfysioloog en performance coach met 20+ jaar ervaring in het analyseren van inspanningstesten voor wielrenners, lopers en triathleten.

**Je expertise:**
- Lactaatmetabolisme en drempelanalyse (D-max, Modified D-max, Lactaat Steady State)
- Hartslagfysiologie en trainingszonering
- Prestatievergelijking met populatienormen (leeftijd, geslacht, niveau)
- Periodisering en trainingsplanning
- VO2max interpretatie en trending

**Je communicatiestijl:**
- Duidelijk, wetenschappelijk onderbouwd maar toegankelijk
- Gebruik concrete cijfers en percentages
- Geef praktische, implementeerbare adviezen
- Wees eerlijk over beperkingen in data
- Motiveer en inspireer de sporter",

    /*
    |--------------------------------------------------------------------------
    | Analysis Templates
    |--------------------------------------------------------------------------
    */

    'analysis_template' => "Analyseer de volgende inspanningstestdata en geef een uitgebreide evaluatie volgens deze structuur:

## 1. TESTOVERZICHT
- Testtype, datum en omstandigheden
- Atlet profiel (leeftijd, gewicht, doelstellingen)

## 2. GEMETEN DREMPELWAARDEN
- **Aërobe drempel (LT1):** [vermogen/snelheid] bij [hartslag] bpm
- **Anaërobe drempel (LT2):** [vermogen/snelheid] bij [hartslag] bpm

## 3. PRESTATIECLASSIFICATIE
Vergelijk met populatienormen

## 4. FYSIOLOGISCHE ANALYSE
Aerobe en anaerobe capaciteit

## 5. TRAININGSZONES UITLEG
Praktische training voorbeelden

## 6. TRAININGSADVIES
Concrete adviezen op basis van doelstellingen

## 7. PROGRESSIE & HERTEST
Verwachte verbeteringen

**TESTDATA:**
{testdata}",

    /*
    |--------------------------------------------------------------------------
    | Population Norms (Normatieve data)
    |--------------------------------------------------------------------------
    */

    'population_norms' => [
        'cycling' => [
            'male' => [
                '30-39' => [
                    'lt1_watt_per_kg' => ['elite' => 3.6, 'good' => 3.0, 'average' => 2.4, 'below' => 1.9],
                    'lt2_watt_per_kg' => ['elite' => 4.6, 'good' => 3.8, 'average' => 3.0, 'below' => 2.4],
                ],
            ],
        ],
        'running' => [
            'male' => [
                '30-39' => [
                    'lt1_speed_kmh' => ['elite' => 14.0, 'good' => 12.0, 'average' => 10.0, 'below' => 8.0],
                    'lt2_speed_kmh' => ['elite' => 16.0, 'good' => 14.0, 'average' => 11.5, 'below' => 9.5],
                ],
            ],
        ],
    ],
];
