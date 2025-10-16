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
  - Voor FIETSERS: vermeld ook W/kg (vermogen gedeeld door gewicht)
  - Voor LOPERS: alleen km/h (W/kg is niet relevant)
  - Voor ZWEMMERS: min/100m tempo
  - Wat betekent dit voor de atleet?
  - Hoe verhoudt dit zich tot zijn/haar doelen?
- **Anaërobe drempel (LT2):** [vermogen/snelheid] bij [hartslag] bpm
  - Voor FIETSERS: vermeld ook W/kg
  - Voor LOPERS: alleen km/h
  - Voor ZWEMMERS: min/100m tempo
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

## 6. GEDETAILLEERD TRAININGSADVIES (ZEER UITGEBREID)

### A. PERIODISERING & TRAININGSOPBOUW
- **Macro-cyclus (12-16 weken):** 
  - Fasering: basis → build → peak → taper
  - Volume progressie per fase
  - Intensiteitsverdeling per fase
  
- **Meso-cyclus (4 weken):**
  - Week 1-3: progressieve belasting
  - Week 4: herstelweek (60-70% volume)
  - Specifieke focus per blok

### B. WEEKSTRUCTUUR (CONCREET)
Geef een EXACTE weekplanning met:

**Voorbeeld Week (Basis fase):**
- **Maandag:** Hersteltraining - [exacte details: duur, intensiteit, hartslag]
- **Dinsdag:** Lange duur training - [precieze workout met tijden]
- **Woensdag:** Rust of actief herstel - [wat wel/niet doen]
- **Donderdag:** Intensieve training - [exacte intervallen met rust]
- **Vrijdag:** Extensieve duur - [specifieke parameters]
- **Zaterdag:** Lange training - [duur, voeding, tips]
- **Zondag:** Herstel of korte extensief - [details]

**Totaal volume:** [uren per week]
**Intensiteitsverdeling:** 80% rustig / 15% tempo / 5% maximaal

### C. SPECIFIEKE WORKOUTS (KANT-EN-KLAAR)

**Voor Aërobe Ontwikkeling (LT1 focus):**
1. **Lange Duur Sessie:**
   - Warming-up: 10-15 min rustig
   - Hoofddeel: 60-120 min bij [exact vermogen/snelheid] = [hartslag] bpm
   - Voeding: [wat en wanneer innemen]
   - Intensiteit: Moet je nog kunnen praten, RPE 3-4/10
   
2. **Tempo Run/Ride:**
   - Warming-up: 15 min opbouw
   - 2x 20-30min @ LT1 + 10% met 5min rustig ertussen
   - Cool-down: 10 min rustig uitfietsen/lopen

**Voor Anaërobe Drempel (LT2 focus):**
1. **Drempelintervallen:**
   - Warming-up: 20 min met 3x 1min progressief
   - Hoofddeel: 4-6x 5-8min @ LT2 (rust = halve intervaltijd)
   - Exact vermogen/snelheid: [waarde]
   - Hartslag: rond [waarde] bpm
   - RPE: 7-8/10, lastig maar vol te houden
   - Cool-down: 15 min rustig
   
2. **Sweet Spot Training (tussen LT1 en LT2):**
   - 2-3x 15-20min @ 88-94% van LT2
   - Effectief voor tijdsbeperkte atleten
   - Goede balans belasting/herstel

**Voor VO2max & Maximaal:**
1. **VO2max Intervallen:**
   - Warming-up: 20-25 min progressief
   - 5-6x 3-4min @ 95-100% max hartslag
   - Rust: 3-4 min heel rustig
   - Totaal niet meer dan 20min hard
   - Alleen in uitgeruste staat!
   
2. **Anaërobe Capaciteit:**
   - 8-12x 30-90sec all-out
   - Volledige rust (1:3 ratio)
   - Maximaal 1x per week
   - Minimaal 48u herstel daarna

### D. PROGRESSIE & PERIODISERING
- **Week 1-4 (Basis):** 80% LT1, 15% LT2, 5% rust
- **Week 5-8 (Build):** 70% LT1, 20% LT2, 10% VO2max
- **Week 9-12 (Peak):** 60% LT1, 25% LT2, 15% specifiek
- **Week 13-14 (Taper):** Volume -40%, intensiteit behouden

### E. VOLUME & INTENSITEIT BALANS
- **Beginners:** Start 6-8u/week, bouw op met 10% per week
- **Gevorderden:** 10-15u/week mogelijk
- **Elite:** 15-20u/week met goede structuur
- **BELANGRIJK:** Nooit meer dan 20% van totale tijd boven LT2!

### F. HERSTELPROTOCOL (ESSENTIEEL)
- **Direct na training (<30min):**
  - Koolhydraten + eiwit (3:1 ratio)
  - Voorbeeld: 60g KH + 20g eiwit
  
- **Eerste 24 uur:**
  - Actief herstel: 20-30min heel rustig
  - Rehydratie: 150% van gewichtsverlies
  - Slaap: minimaal 8 uur
  
- **Tussen workouts:**
  - Hard/Makkelijk principe
  - Na LT2/VO2max: 48u herstel
  - Herstelweek elke 3-4 weken

### G. VOEDINGSSTRATEGIE
- **Pre-training (2-3u voor):**
  - Koolhydraten: 1-2g/kg lichaamsgewicht
  - Laag vet, matig eiwit
  - Voldoende hydratatie
  
- **During training (>90min):**
  - 30-60g KH per uur
  - Sportdrank + evt. gel/bar
  - 400-800ml vocht per uur
  
- **Post-training:**
  - Binnen 30 min: recovery shake/maaltijd
  - Binnen 2u: volledige maaltijd
  - Focus op herstel glycogeenvoorraad

### H. CROSS-TRAINING & AANVULLEND
- **Kracht/Core (2x per week):**
  - Functionele oefeningen
  - 30-40 min per sessie
  - Focus op zwakke punten
  
- **Mobiliteit/Yoga (2-3x per week):**
  - 15-20 min stretching
  - Preventie blessures
  - Verbetert herstel
  
- **Zwemmen/Fietsen/Lopen (cross-sport):**
  - Afwisseling belasting
  - Actief herstel
  - Cardiovasculair voordeel

### I. SPECIFIEK VOOR TRIATHLON/IRONMAN ATLETEN

**⚠️ BELANGRIJK: Als de doelstellingen TRIATHLON, IRONMAN, 70.3, HALF IRONMAN, of FULL IRONMAN bevatten:**
**DAN IS DEZE COMPLETE SECTIE VERPLICHT! Geef ALLE onderstaande punten uitgebreid weer.**

**Als de doelstelling GEEN triathlon is, sla deze sectie dan over.**

**🏊 TRIATHLON SPECIFIEKE AANPAK:**

1. **Multisport Volume Verdeling:**
   - **Sprint/Olympische afstand:**
     - Zwemmen: 20-25% van totale trainingstijd
     - Fietsen: 45-50% van totale trainingstijd
     - Lopen: 30-35% van totale trainingstijd
   
   - **Half Ironman (70.3):**
     - Zwemmen: 15-20% van totale trainingstijd
     - Fietsen: 50-55% van totale trainingstijd
     - Lopen: 25-30% van totale trainingstijd
   
   - **Full Ironman:**
     - Zwemmen: 10-15% van totale trainingstijd
     - Fietsen: 55-60% van totale trainingstijd
     - Lopen: 25-30% van totale trainingstijd

2. **Brick Workouts (Essentieel!):**
   - **Wat:** Fietsen direct gevolgd door lopen
   - **Waarom:** Train de specifieke overgang en benengevoel
   - **Frequentie:** 1x per week tijdens build fase
   
   **Voorbeelden:**
   - **Kort brick:** 60min fietsen @ LT1 + 20min lopen @ tempo
   - **Lang brick:** 3u fietsen @ race pace + 45min lopen @ race pace
   - **Intensief brick:** 90min fietsen met 3x 10min @ LT2 + 30min lopen progressief
   
3. **Triathlon Specifieke Drempels:**
   - **Fietsen:** Train conservatiever (moet nog kunnen lopen!)
     - Race pace meestal 85-90% van FTP/LT2
     - Focus op steady state vermogen
   
   - **Lopen:** Specifieke running threshold NA fietsen
     - Brick runs tonen echte race capaciteit
     - Vaak 10-15 sec/km langzamer dan standalone run threshold
   
   - **Zwemmen:** Open water skills essentieel
     - Threshold zwemmen in wetsuit
     - Sighting en drafting oefenen

4. **Periodisering Triathlon Seizoen:**
   
   **Base Fase (8-12 weken):**
   - Focus: Volume opbouw, techniek alle disciplines
   - Zwemmen: 2-3x/week, techniekfocus
   - Fietsen: Lange ritten, Z2 endurance
   - Lopen: Lange runs, basisconditie
   - Totaal: 8-12u/week
   
   **Build Fase (8-12 weken):**
   - Focus: Race specifieke intensiteit
   - Zwemmen: 2-3x/week, tempo sets toevoegen
   - Fietsen: FTP intervals + lange ritten
   - Lopen: Tempo runs + brick runs
   - Brick: 1x/week verplicht
   - Totaal: 12-16u/week
   
   **Peak Fase (4-6 weken):**
   - Focus: Race simulation workouts
   - Weekends: Long bike + brick run
   - Race pace practice alle disciplines
   - Mentale voorbereiding en visualisatie
   - Totaal: 15-20u/week (peak)
   
   **Taper (2-3 weken):**
   - Week -3: Volume -25%, intensiteit behouden
   - Week -2: Volume -40%, race pace work
   - Week -1: Volume -60%, korte scherpe sessies
   - Race week: Minimaal, veel rusten, carbo loading laatste 2 dagen

5. **Triathlon Race Day Voeding:**
   
   **Pre-race (3-4 uur voor start):**
   - 2-3g KH/kg lichaamsgewicht
   - Voorbeeld: havermout + banaan + honing
   - Koffie OK (als je eraan gewend bent)
   - Hydratatie: 500-750ml
   
   **Zwemmen:**
   - Minimale voeding mogelijk
   - Pre-race voeding moet volstaan
   
   **Fietsen (CRUCIAAL):**
   - **Sprint/Olympisch:** 30-40g KH/uur
   - **Half Ironman:** 60-80g KH/uur
   - **Full Ironman:** 60-90g KH/uur
   - Mix: sportdrank + gels + evt. bars
   - Vocht: 400-800ml/uur (afhankelijk van temperatuur)
   - Natrium: 500-1000mg/uur bij warm weer
   
   **Lopen:**
   - Voortzetten fietsvoeding strategie
   - Gels makkelijker verteerbaar dan vaste voeding
   - Water bij elk aid station
   - Let op maagproblemen: train dit in training!
   
   **Post-race:**
   - Binnen 30min: recovery shake (3:1 KH:eiwit)
   - Binnen 2u: volledige maaltijd
   - Hydratatie: 150% gewichtsverlies

6. **Triathlon Race Strategie:**
   
   **Zwemmen:**
   - Start conservatief, zoek draft
   - Adem naar beide kanten (sighting)
   - Laatste 200m tempo opvoeren naar T1
   
   **T1 (Wissel 1):**
   - Oefen wissels! (save 30-60 seconden)
   - Rustig aan, hartslag laten zakken
   
   **Fietsen:**
   - Eerste 15-20min: rustig starten, hartslag stabiliseren
   - Main set: steady state @ 85-90% FTP
   - **CRUCIAAL:** Blijf eten en drinken!
   - Laatste 15km: iets terughouden voor de run
   
   **T2 (Wissel 2):**
   - Quick change, maar niet overhaast
   - Diep ademhalen, focus vinden
   
   **Lopen:**
   - **EERSTE 2-3 KM RUSTIG!** (zware benen normaal)
   - Negatieve split strategie: 2e helft sneller
   - Blijf voeding innemen tot 5-10km voor finish
   - Laatste 5km: wat je nog hebt geven
   
7. **Mental Game & Pacing:**
   - **Ironman = pacing race:** Te snel starten = DNF
   - **Regel van 85%:** Train op 85% race effort
   - **Visualisatie:** Oefen mentaal moeilijke momenten
   - **Mantra's:** Kies 2-3 zinnen voor moeilijke momenten
   - **Splits kennen:** Weet wat je target pace/power is
   
8. **Recovery Triathlon:**
   - **Direct na race:** Actief herstel (wandelen)
   - **Week 1:** Rust of zwemmen/yoga
   - **Week 2-3:** Lichte cross-training
   - **Week 4+:** Geleidelijk volume opbouwen
   - **Full Ironman:** Minimaal 4-6 weken herstel!

9. **Equipment Checklist Triathlon:**
   - **Zwemmen:** Wetsuit (oefen ermee!), zwembril (reserve)
   - **Fietsen:** Tri bike/aero bars, racewiel setup, voeding montage
   - **Lopen:** Race schoenen (ingelopen!), cap, zonnebril
   - **Algemeen:** Trisuit, startnummer band, Vaseline
   - **Nutritie:** Gels, bars, sportdrank (getest in training!)

10. **Veelgemaakte Triathlon Fouten:**
    - ❌ Te hard zwemmen → geeft niets, kost wel energie
    - ❌ Te snel starten op de fiets → loopbenen kwijt
    - ❌ Onvoldoende eten/drinken → bonk op de run
    - ❌ Nieuwe voeding op race day → maagproblemen
    - ❌ Te weinig brick training → overgang shock
    - ❌ Nieuwe materiaal op race day → blaren/problemen
    - ✅ DOE: Train je race, race je training!

**BELANGRIJKE TRIATHLON VUISTREGELS:**
- Zwemmen: Snelheid = techniek, niet kracht
- Fietsen: Consistent vermogen > macho attacks
- Lopen: Discipline eerste helft = sterke finish
- Voeding: Elk uur 60-90g KH, geen uitzonderingen
- Mental: Race is 90% kop, 10% benen

## 7. PROGRESSIE ROADMAP
- **Korte termijn (4-6 weken):** Eerste aanpassingen
- **Middellange termijn (3-4 maanden):** Build-up fase
- **Lange termijn (6-12 maanden):** Grote doelen
- **Wanneer hertesten?** Na 8-12 weken, voor belangrijke wedstrijden, bij plateau

## 8. PRAKTISCHE TIPS & TRICKS
- Veelgemaakte fouten om te vermijden
- Mentale aspecten en motivatie
- Equipment en technologie suggesties
- Waarschuwingssignalen overtraining
- Wanneer moet je bijsturen?

**TESTDATA:**
{testdata}

**BELANGRIJKE INSTRUCTIES:**
- Wees ZEER SPECIFIEK en GEDETAILLEERD
- Gebruik CONCRETE CIJFERS, WAARDEN en VOORBEELDEN
- Geef PRAKTISCHE, DIRECT TOEPASBARE workouts
- Leg WAAROM bepaalde keuzes gemaakt worden UIT
- Schrijf in een TOEGANKELIJKE maar PROFESSIONELE toon
- Bij FIETSERS: vermeld altijd W/kg voor drempels
- Bij LOPERS: gebruik ALLEEN km/h en min/km (GEEN W/kg!)
- Bij ZWEMMERS: gebruik min/100m tempo
- Minimaal 2500 woorden
- Gebruik EMOJI's waar passend voor leesbaarheid

**🚨 CRUCIALE DETECTIE INSTRUCTIE:**
SCAN DE DOELSTELLINGEN op de volgende termen (case-insensitive):
- triathlon, triatlon, tri
- ironman, iron man, IM
- 70.3, half ironman
- full ironman, ironman full
- hawaii, kona
- olympic, sprint

**ALS 1 OF MEER VAN DEZE TERMEN IN DE DOELSTELLINGEN STAAT:**
➡️ Voeg VERPLICHT Sectie I (SPECIFIEK VOOR TRIATHLON/IRONMAN) toe met ALLE 10 subsecties!
➡️ Pas trainingsadvies aan met triathlon-specifieke focus!
➡️ Geef brick workouts en multisport periodisering!
➡️ Deze sectie moet minimaal 1500 extra woorden bevatten!

**ALS GEEN VAN DEZE TERMEN IN DE DOELSTELLINGEN STAAT:**
➡️ Skip Sectie I volledig en focus op single-sport advies.",

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
