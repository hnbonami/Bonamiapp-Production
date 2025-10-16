{{-- Drempelwaarden Overzicht - Rapport Versie (Print-ready) --}}
{{-- Variabele sleutel: {{INSPANNINGSTEST_DREMPELS}} --}}

<style>
    .rapport-drempelwaarden {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #1f2937;
        width: 120%;
    }
    
    .rapport-drempelwaarden h3 {
        font-size: 14px;
        font-weight: 700;
        color: #0f4c75;
        margin: 15px 0 10px 0;
        padding: 8px 10px;
        background-color: #c8e1eb;
        border-left: 4px solid #0f4c75;
    }
    
    .drempelwaarden-table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
        background: white;
    }
    
    .drempelwaarden-table thead {
        background-color: #e3f2fd;
    }
    
    .drempelwaarden-table th {
        padding: 6px 8px;
        text-align: center;
        font-weight: 700;
        font-size: 10px;
        color: #374151;
        border-bottom: 2px solid #c8e1eb;
    }
    
    .drempelwaarden-table td {
        padding: 5px 8px;
        font-size: 11px;
        color: #1f2937;
        border-bottom: 1px solid #e5e7eb;
        text-align: center;
    }
    
    .drempelwaarden-table tbody tr:nth-child(odd) {
        background-color: #ffffff;
    }
    
    .drempelwaarden-table tbody tr:nth-child(even) {
        background-color: #f9fafb;
    }
    
    .rapport-evaluatie-box {
        margin: 10px 0;
        padding: 10px 12px;
        background: #f8f6eeff;
        border-left: 4px solid #fcdaa0ff;
        font-size: 10px;
        line-height: 1.5;
        color: #78350f;
    }
    
    .rapport-evaluatie-box strong {
        font-weight: 700;
        color: #92400e;
    }
</style>

@php
    // Bepaal testtype
    $testtype = $inspanningstest->testtype ?? 'fietstest';
    $isLooptest = str_contains($testtype, 'loop') || str_contains($testtype, 'lopen');
    $isZwemtest = str_contains($testtype, 'zwem');
    
    // Haal testresultaten op via relatie of decode JSON
    $testresultaten = $inspanningstest->testresultaten ?? [];
    
    // Check of testresultaten een string is (JSON) en decode indien nodig
    if (is_string($testresultaten)) {
        $testresultaten = json_decode($testresultaten, true) ?? [];
    }
    
    // Converteer naar array voor consistentie
    $testresultaten = is_array($testresultaten) ? $testresultaten : [];
    
    // BEREKEN snelheid voor veldtesten als die niet aanwezig is (exact zoals _testresultaten.blade.php)
    $isVeldtest = str_contains($testtype, 'veld');
    $testresultaten = array_map(function($stap) use ($isVeldtest, $isLooptest, $isZwemtest) {
        // Converteer naar array als het een object is
        $stap = is_array($stap) ? $stap : (array)$stap;
        
        // Als snelheid al aanwezig is, gebruik die
        if (isset($stap['snelheid']) && $stap['snelheid'] !== null && $stap['snelheid'] !== '') {
            return $stap;
        }
        
        // Voor veldtesten: bereken snelheid uit afstand en tijd
        if ($isVeldtest && ($isLooptest || $isZwemtest)) {
            $afstand = floatval($stap['afstand'] ?? 0); // in meters
            $tijdMin = floatval($stap['tijd_min'] ?? 0);
            $tijdSec = floatval($stap['tijd_sec'] ?? 0);
            $tijdUren = ($tijdMin + ($tijdSec / 60)) / 60; // converteer naar uren
            
            // Snelheid = afstand (km) / tijd (uren)
            if ($tijdUren > 0) {
                $stap['snelheid'] = ($afstand / 1000) / $tijdUren;
            }
        }
        
        return $stap;
    }, $testresultaten);
    
    // Haal drempelwaarden op
    $lt1Vermogen = $inspanningstest->aerobe_drempel_vermogen ?? null;
    $lt1Hartslag = $inspanningstest->aerobe_drempel_hartslag ?? null;
    $lt2Vermogen = $inspanningstest->anaerobe_drempel_vermogen ?? null;
    $lt2Hartslag = $inspanningstest->anaerobe_drempel_hartslag ?? null;
    
    // Maximum waarden uit laatste testresultaat
    $maxVermogen = null;
    $maxLactaat = null;
    $maxHartslag = null;
    
    if (count($testresultaten) > 0) {
        $laatsteStap = end($testresultaten);
        $maxVermogen = $laatsteStap['vermogen'] ?? $laatsteStap['snelheid'] ?? null;
        $maxLactaat = $laatsteStap['lactaat'] ?? null;
        $maxHartslag = $laatsteStap['hartslag'] ?? null;
    }
    
    // Fallback naar ingevuld max hartslag veld
    if (!$maxHartslag) {
        $maxHartslag = $inspanningstest->maximale_hartslag_bpm ?? null;
    }
    
    // Lichaamsgewicht voor Watt/kg berekening
    $gewicht = $inspanningstest->lichaamsgewicht_kg ?? null;
    
    // Bereken Watt/kg voor fietstesten
    $lt1WattPerKg = null;
    $lt2WattPerKg = null;
    $maxWattPerKg = null;
    
    if ($gewicht && $gewicht > 0) {
        if ($lt1Vermogen) $lt1WattPerKg = $lt1Vermogen / $gewicht;
        if ($lt2Vermogen) $lt2WattPerKg = $lt2Vermogen / $gewicht;
        if ($maxVermogen) $maxWattPerKg = $maxVermogen / $gewicht;
    }
    
    // Bereken percentages van max
    function berekenPercentageReport($waarde, $max) {
        if (!$waarde || !$max || $max == 0) return null;
        return round(($waarde / $max) * 100);
    }
    
    $lt1Percentage = berekenPercentageReport($lt1Hartslag, $maxHartslag);
    $lt2Percentage = berekenPercentageReport($lt2Hartslag, $maxHartslag);
    
    // Haal lactaat waarden op bij drempels uit testresultaten
    $lt1Lactaat = null;
    $lt2Lactaat = null;
    
    if ($lt1Vermogen && count($testresultaten) > 0) {
        $closestDiff = PHP_INT_MAX;
        foreach ($testresultaten as $stap) {
            $stapVermogen = $stap['vermogen'] ?? $stap['snelheid'] ?? null;
            if ($stapVermogen) {
                $diff = abs($stapVermogen - $lt1Vermogen);
                if ($diff < $closestDiff) {
                    $closestDiff = $diff;
                    $lt1Lactaat = $stap['lactaat'] ?? null;
                }
            }
        }
    }
    
    if ($lt2Vermogen && count($testresultaten) > 0) {
        $closestDiff = PHP_INT_MAX;
        foreach ($testresultaten as $stap) {
            $stapVermogen = $stap['vermogen'] ?? $stap['snelheid'] ?? null;
            if ($stapVermogen) {
                $diff = abs($stapVermogen - $lt2Vermogen);
                if ($diff < $closestDiff) {
                    $closestDiff = $diff;
                    $lt2Lactaat = $stap['lactaat'] ?? null;
                }
            }
        }
    }
    
    // Helper functies
    function formatZwemTijdReport($decimaleMinuten) {
        if (!$decimaleMinuten) return null;
        $totalSeconds = $decimaleMinuten * 60;
        $min = floor($totalSeconds / 60);
        $sec = round($totalSeconds % 60);
        return sprintf('%d:%02d', $min, $sec);
    }
    
    function berekenMinPerKmReport($kmh) {
        if (!$kmh || $kmh == 0) return null;
        $decimalMinutes = 60 / $kmh;
        $minutes = floor($decimalMinutes);
        $seconds = round(($decimalMinutes - $minutes) * 60);
        return sprintf('%d:%02d', $minutes, $seconds);
    }
@endphp

<div class="rapport-drempelwaarden">
    <h3>📈 Drempelwaarden Overzicht</h3>
    <p style="font-size: 10px; color: #6b7280; margin: 8px 0;">Samenvatting van gemeten prestatieparameters bij aërobe en anaërobe drempel</p>
    
    <table class="drempelwaarden-table">
        <thead>
            <tr>
                <th>Drempel</th>
                @if($isLooptest)
                    <th>Snelheid<br><span style="font-size: 9px; font-weight: normal;">(km/h)</span></th>
                    <th>Tempo<br><span style="font-size: 9px; font-weight: normal;">(min/km)</span></th>
                @elseif($isZwemtest)
                    <th>Tempo<br><span style="font-size: 9px; font-weight: normal;">(min/100m)</span></th>
                @else
                    <th>Vermogen<br><span style="font-size: 9px; font-weight: normal;">(W)</span></th>
                    <th>Vermogen<br><span style="font-size: 9px; font-weight: normal;">(W/kg)</span></th>
                @endif
                <th>Hartslag<br><span style="font-size: 9px; font-weight: normal;">(bpm)</span></th>
                <th>Lactaat<br><span style="font-size: 9px; font-weight: normal;">(mmol/L)</span></th>
                <th>% Max</th>
            </tr>
        </thead>
        <tbody>
            {{-- Aërobe Drempel (LT1) --}}
            <tr>
                <td style="text-align: left; font-weight: 700;"><span style="color: #dc2626;">🔴</span> Aërobe drempel</td>
                @if($isLooptest)
                    <td style="color: #2563eb; font-weight: 600;">{{ $lt1Vermogen ? number_format($lt1Vermogen, 1) : '-' }}</td>
                    <td style="color: #2563eb; font-weight: 600;">{{ $lt1Vermogen ? berekenMinPerKmReport($lt1Vermogen) : '-' }}</td>
                @elseif($isZwemtest)
                    <td style="color: #2563eb; font-weight: 600;">{{ $lt1Vermogen ? formatZwemTijdReport($lt1Vermogen) : '-' }}</td>
                @else
                    <td style="color: #2563eb; font-weight: 600;">{{ $lt1Vermogen ? round($lt1Vermogen) : '-' }}</td>
                    <td style="color: #2563eb; font-weight: 600;">{{ $lt1WattPerKg ? number_format($lt1WattPerKg, 1) : '-' }}</td>
                @endif
                <td style="color: #dc2626; font-weight: 600;">{{ $lt1Hartslag ? round($lt1Hartslag) : '-' }}</td>
                <td style="color: #16a34a; font-weight: 600;">{{ $lt1Lactaat !== null ? number_format($lt1Lactaat, 1) : '-' }}</td>
                <td style="color: #6b7280; font-weight: 600;">{{ $lt1Percentage ? $lt1Percentage . '%' : '-' }}</td>
            </tr>
            
            {{-- Anaërobe Drempel (LT2) --}}
            <tr>
                <td style="text-align: left; font-weight: 700;"><span style="color: #f59e0b;">🟠</span> Anaërobe drempel</td>
                @if($isLooptest)
                    <td style="color: #2563eb; font-weight: 600;">{{ $lt2Vermogen ? number_format($lt2Vermogen, 1) : '-' }}</td>
                    <td style="color: #2563eb; font-weight: 600;">{{ $lt2Vermogen ? berekenMinPerKmReport($lt2Vermogen) : '-' }}</td>
                @elseif($isZwemtest)
                    <td style="color: #2563eb; font-weight: 600;">{{ $lt2Vermogen ? formatZwemTijdReport($lt2Vermogen) : '-' }}</td>
                @else
                    <td style="color: #2563eb; font-weight: 600;">{{ $lt2Vermogen ? round($lt2Vermogen) : '-' }}</td>
                    <td style="color: #2563eb; font-weight: 600;">{{ $lt2WattPerKg ? number_format($lt2WattPerKg, 1) : '-' }}</td>
                @endif
                <td style="color: #dc2626; font-weight: 600;">{{ $lt2Hartslag ? round($lt2Hartslag) : '-' }}</td>
                <td style="color: #16a34a; font-weight: 600;">{{ $lt2Lactaat !== null ? number_format($lt2Lactaat, 1) : '-' }}</td>
                <td style="color: #6b7280; font-weight: 600;">{{ $lt2Percentage ? $lt2Percentage . '%' : '-' }}</td>
            </tr>
            
            {{-- Maximum --}}
            @if($maxVermogen || $maxHartslag || $maxLactaat)
                <tr>
                    <td style="text-align: left; font-weight: 700;"><span style="color: #ef4444;">🔥</span> Maximum</td>
                    @if($isLooptest)
                        <td style="color: #2563eb; font-weight: 600;">{{ $maxVermogen ? number_format($maxVermogen, 1) : '-' }}</td>
                        <td style="color: #2563eb; font-weight: 600;">{{ $maxVermogen ? berekenMinPerKmReport($maxVermogen) : '-' }}</td>
                    @elseif($isZwemtest)
                        <td style="color: #2563eb; font-weight: 600;">{{ $maxVermogen ? formatZwemTijdReport($maxVermogen) : '-' }}</td>
                    @else
                        <td style="color: #2563eb; font-weight: 600;">{{ $maxVermogen ? round($maxVermogen) : '-' }}</td>
                        <td style="color: #2563eb; font-weight: 600;">{{ $maxWattPerKg ? number_format($maxWattPerKg, 1) : '-' }}</td>
                    @endif
                    <td style="color: #dc2626; font-weight: 600;">{{ $maxHartslag ? round($maxHartslag) : '-' }}</td>
                    <td style="color: #16a34a; font-weight: 600;">{{ $maxLactaat ? number_format($maxLactaat, 1) : '-' }}</td>
                    <td style="color: #6b7280; font-weight: 600;">100%</td>
                </tr>
            @endif
        </tbody>
    </table>
    
    <div style="text-align: right; font-size: 9px; color: #6b7280; margin: 8px 0;">
        <strong>LT1:</strong> Aërobe drempel (lactaat ~2.0 mmol/L) · 
        <strong>LT2:</strong> Anaërobe drempel (lactaat ~4.0 mmol/L)
    </div>
    
    {{-- Evaluatie box met uitgebreide uitleg --}}
    <div class="rapport-evaluatie-box">
        <strong>💡 Wat betekenen deze drempelwaarden?</strong>
        <p style="margin-top: 6px;">
            De drempelwaarden zijn de belangrijkste uitkomsten van je test. Ze tonen de overgangsmomenten tussen verschillende energiesystemen in je lichaam en vormen de basis voor je trainingszones.
        </p>
        
        <p style="margin-top: 8px;">
            <strong>🔴 Aërobe drempel (LT1):</strong> 
            Het punt waarop je lichaam van pure aërobe naar gecombineerde energie overschakelt. 
            @if($isLooptest)
                Tot deze snelheid kun je zeer lang volhouden (meerdere uren) - het voelt comfortabel aan en je kunt nog makkelijk praten. 
                Ideaal voor duurtraining om je basis op te bouwen.
            @elseif($isZwemtest)
                Tot dit tempo kun je zeer lang volhouden - het voelt comfortabel en je ademhaling blijft rustig. 
                Ideaal voor duurtraining om je basis op te bouwen.
            @else
                Tot dit vermogen kun je zeer lang volhouden (meerdere uren) - het voelt comfortabel aan. 
                Ideaal voor lange tochten en basisconditie.
            @endif
        </p>
        
        <p style="margin-top: 6px;">
            <strong>🟠 Anaërobe drempel (LT2):</strong> 
            Het punt waarop je lichaam meer lactaat produceert dan het kan afbreken. Boven deze drempel "verzuur" je en kun je slechts beperkte tijd volhouden. 
            @if($isLooptest)
                Deze snelheid kun je 30-60 minuten volhouden bij maximale inspanning - denk aan wedstrijdtempo. 
                Het voelt "zwaar maar haalbaar", praten wordt moeilijk.
            @elseif($isZwemtest)
                Dit tempo kun je 30-60 minuten volhouden bij maximale inspanning - je zwemtechniek vergt meer concentratie. 
                Je maximale steady-state tempo.
            @else
                Dit vermogen kun je 30-60 minuten volhouden - denk aan tijdrit of bergrit tempo. 
                Je maximale steady-state vermogen.
            @endif
        </p>
        
        <p style="margin-top: 6px;">
            <strong>🔥 Maximum:</strong> De hoogste waarden tijdens je test - toont je piekprestatie en lactaattolerantie in uitgeruste toestand.
        </p>
        
        <p style="margin-top: 8px; padding-top: 6px; border-top: 1px solid #f59e0b;">
            <strong>📊 Kolom uitleg:</strong> 
            @if($isLooptest)
                <em>Snelheid (km/h)</em> = directe trainingswaarde · 
                <em>Tempo (min/km)</em> = praktisch voor planning · 
            @elseif($isZwemtest)
                <em>Tempo (min/100m)</em> = standaard zwemwaarde · 
            @else
                <em>Vermogen (W)</em> = nauwkeurigste inspanningsmaat · 
                <em>W/kg</em> = vergelijkbaar bij gewichtsverandering · 
            @endif
            <em>Hartslag</em> = handig zonder powermeter (varieert door warmte/vermoeidheid) · 
            <em>Lactaat</em> = objectieve maat voor metabolisme · 
            <em>%max</em> = verhouding tot maximum (LT2 vaak 85-95% bij goed getrainden).
        </p>
        
        <p style="margin-top: 6px;">
            <strong>💪 Gebruik:</strong> Deze waarden zijn de basis voor je persoonlijke trainingszones. 
            Vergelijk bij volgende testen - als LT1 en LT2 hoger worden, word je fitter!
        </p>
    </div>
</div>
