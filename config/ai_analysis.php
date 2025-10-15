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
    
    'temperature' => env('AI_TEMPERATURE', 0.7),
    
    'max_tokens' => env('AI_MAX_TOKENS', 3000),
    
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

    'analysis_template' => "Analyseer de volgende inspanningstestdata en geef een ZEER UITGEBREIDE evaluatie volgens deze structuur:

## 1. TESTOVERZICHT & CONTEXT
- Testtype, datum en omstandigheden
- Atlet profiel (leeftijd, gewicht, doelstellingen)
- Wat wilde de atleet bereiken met deze test?

## 2. GEMETEN DREMPELWAARDEN (GEDETAILLEERD)
- **Aërobe drempel (LT1):** [vermogen/snelheid] bij [hartslag] bpm
  - Wat betekent dit voor de atleet?
  - Hoe verhoudt dit zich tot zijn/haar doelen?
- **Anaërobe drempel (LT2):** [vermogen/snelheid] bij [hartslag] bpm
  - Interpretatie en betekenis
  - Praktische implicaties

## 3. PRESTATIECLASSIFICATIE & VERGELIJKING
- Vergelijk met populatienormen (leeftijdsgroep, geslacht)
- Sterktes en verbeterpunten
- Realistische verwachtingen

## 4. DIEPGAANDE FYSIOLOGISCHE ANALYSE
- Aerobe capaciteit en uithoudingsvermogen
- Anaerobe capaciteit en sprintvermogen
- Lactaatclearance en herstelcapaciteit
- Efficiëntie en loopeconomie/fietseconomie
- Hartslagrespons en cardiovasculaire fitness

## 5. TRAININGSZONES UITLEG (PRAKTISCH)
Voor elke zone:
- Doel van de zone
- Praktische voorbeelden
- Hoe voelt het aan? (RPE/Borg)
- Voorbeeldworkouts

## 6. GEDETAILLEERD TRAININGSADVIES
Op basis van de doelstellingen:
- **Periodisering:** Hoe de training opbouwen?
- **Weekstructuur:** Concrete week voorbeelden
- **Specifieke workouts:** Exacte intervallen en duur
- **Volume vs Intensiteit:** Balans vinden
- **Herstelprotocol:** Rust en regeneratie
- **Voeding:** Pre/tijdens/post training
- **Cross-training:** Aanvullende activiteiten

## 7. PROGRESSIE ROADMAP
- **Korte termijn (4-6 weken):** Eerste aanpassingen
- **Middellange termijn (3-4 maanden):** Build-up fase
- **Lange termijn (6-12 maanden):** Grote doelen
- **Wanneer hertesten?** Timing en redenen

## 8. PRAKTISCHE TIPS & TRICKS
- Veelgemaakte fouten om te vermijden
- Mentale aspecten en motivatie
- Equipment en technologie suggesties
- Wanneer moet je bijsturen?

**TESTDATA:**
{testdata}

**BELANGRIJKE INSTRUCTIES:**
- Wees ZEER SPECIFIEK en GEDETAILLEERD
- Gebruik CONCRETE CIJFERS en VOORBEELDEN
- Geef PRAKTISCHE, IMPLEMENTEERBARE adviezen
- Motiveer WAAROM bepaalde keuzes gemaakt worden
- Schrijf in een TOEGANKELIJKE maar PROFESSIONELE toon
- Minimaal 2000 woorden
- Gebruik EMOJI's waar passend voor leesbaarheid",

    /*
    |--------------------------------------------------------------------------
    | Population Norms (Normatieve data)
    |--------------------------------------------------------------------------
    */

    'population_norms' => [
        'cycling' => [
            'male' => [
                '18-29' => [
                    'lt1_watt_per_kg' => ['elite' => 3.8, 'good' => 3.2, 'average' => 2.6, 'below' => 2.0],
                    'lt2_watt_per_kg' => ['elite' => 4.8, 'good' => 4.0, 'average' => 3.2, 'below' => 2.5],
                ],
                '30-39' => [
                    'lt1_watt_per_kg' => ['elite' => 3.6, 'good' => 3.0, 'average' => 2.4, 'below' => 1.9],
                    'lt2_watt_per_kg' => ['elite' => 4.6, 'good' => 3.8, 'average' => 3.0, 'below' => 2.4],
                ],
                '40-49' => [
                    'lt1_watt_per_kg' => ['elite' => 3.4, 'good' => 2.8, 'average' => 2.2, 'below' => 1.7],
                    'lt2_watt_per_kg' => ['elite' => 4.4, 'good' => 3.6, 'average' => 2.8, 'below' => 2.2],
                ],
                '50+' => [
                    'lt1_watt_per_kg' => ['elite' => 3.2, 'good' => 2.6, 'average' => 2.0, 'below' => 1.5],
                    'lt2_watt_per_kg' => ['elite' => 4.2, 'good' => 3.4, 'average' => 2.6, 'below' => 2.0],
                ],
            ],
            'female' => [
                '18-29' => [
                    'lt1_watt_per_kg' => ['elite' => 3.2, 'good' => 2.6, 'average' => 2.0, 'below' => 1.5],
                    'lt2_watt_per_kg' => ['elite' => 4.0, 'good' => 3.2, 'average' => 2.5, 'below' => 1.9],
                ],
                '30-39' => [
                    'lt1_watt_per_kg' => ['elite' => 3.0, 'good' => 2.4, 'average' => 1.8, 'below' => 1.3],
                    'lt2_watt_per_kg' => ['elite' => 3.8, 'good' => 3.0, 'average' => 2.3, 'below' => 1.7],
                ],
                '40-49' => [
                    'lt1_watt_per_kg' => ['elite' => 2.8, 'good' => 2.2, 'average' => 1.6, 'below' => 1.2],
                    'lt2_watt_per_kg' => ['elite' => 3.6, 'good' => 2.8, 'average' => 2.1, 'below' => 1.6],
                ],
                '50+' => [
                    'lt1_watt_per_kg' => ['elite' => 2.6, 'good' => 2.0, 'average' => 1.5, 'below' => 1.1],
                    'lt2_watt_per_kg' => ['elite' => 3.4, 'good' => 2.6, 'average' => 2.0, 'below' => 1.5],
                ],
            ],
        ],
        'running' => [
            'male' => [
                '18-29' => [
                    'lt1_speed_kmh' => ['elite' => 14.5, 'good' => 12.5, 'average' => 10.5, 'below' => 8.5],
                    'lt2_speed_kmh' => ['elite' => 16.5, 'good' => 14.5, 'average' => 12.0, 'below' => 10.0],
                ],
                '30-39' => [
                    'lt1_speed_kmh' => ['elite' => 14.0, 'good' => 12.0, 'average' => 10.0, 'below' => 8.0],
                    'lt2_speed_kmh' => ['elite' => 16.0, 'good' => 14.0, 'average' => 11.5, 'below' => 9.5],
                ],
                '40-49' => [
                    'lt1_speed_kmh' => ['elite' => 13.5, 'good' => 11.5, 'average' => 9.5, 'below' => 7.5],
                    'lt2_speed_kmh' => ['elite' => 15.5, 'good' => 13.5, 'average' => 11.0, 'below' => 9.0],
                ],
                '50+' => [
                    'lt1_speed_kmh' => ['elite' => 13.0, 'good' => 11.0, 'average' => 9.0, 'below' => 7.0],
                    'lt2_speed_kmh' => ['elite' => 15.0, 'good' => 13.0, 'average' => 10.5, 'below' => 8.5],
                ],
            ],
            'female' => [
                '18-29' => [
                    'lt1_speed_kmh' => ['elite' => 12.5, 'good' => 10.5, 'average' => 8.5, 'below' => 7.0],
                    'lt2_speed_kmh' => ['elite' => 14.0, 'good' => 12.0, 'average' => 10.0, 'below' => 8.0],
                ],
                '30-39' => [
                    'lt1_speed_kmh' => ['elite' => 12.0, 'good' => 10.0, 'average' => 8.0, 'below' => 6.5],
                    'lt2_speed_kmh' => ['elite' => 13.5, 'good' => 11.5, 'average' => 9.5, 'below' => 7.5],
                ],
                '40-49' => [
                    'lt1_speed_kmh' => ['elite' => 11.5, 'good' => 9.5, 'average' => 7.5, 'below' => 6.0],
                    'lt2_speed_kmh' => ['elite' => 13.0, 'good' => 11.0, 'average' => 9.0, 'below' => 7.0],
                ],
                '50+' => [
                    'lt1_speed_kmh' => ['elite' => 11.0, 'good' => 9.0, 'average' => 7.0, 'below' => 5.5],
                    'lt2_speed_kmh' => ['elite' => 12.5, 'good' => 10.5, 'average' => 8.5, 'below' => 6.5],
                ],
            ],
        ],
    ],
];
