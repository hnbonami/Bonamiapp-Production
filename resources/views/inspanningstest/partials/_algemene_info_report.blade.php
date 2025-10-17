{{-- Algemene Info + Lichaamssam    .rapport-algemene-info h3 {
        font-size: 16px;
        font-weight: 700;
        color: #0f4c75;
        margin: 15px 0 10px 0;
        padding: 8px 10px;
        background-color: #f0f9ffff;
        border-left: 4px solid #c8e1eb;
        border-radius: 8px;
    }
    
    .rapport-doelstellingen-box {
        margin: 10px 0;
        padding: 10px;
        background: #f0f9ff;
        border-left: 3px solid #c8e1eb;
        font-size: 13px;
        line-height: 1.6;
        border-radius: 8px;
    } Versie (Print-ready) --}}
<style>
    .rapport-algemene-info {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 13px;
        line-height: 1.5;
        color: #1f2937;
        width: 120%;
    }
    
    .rapport-algemene-info table {
        width: 120%;
        border-collapse: collapse;
        margin: 10px 0;
    }
    
    .rapport-algemene-info td {
        padding: 6px 10px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top;
    }
    
    .rapport-algemene-info td:first-child {
        font-weight: 600;
        width: 35%;
        color: #374151;
    }
    
    .rapport-algemene-info td:last-child {
        color: #1f2937;
    }
    
    .rapport-algemene-info h3 {
        font-size: 14px;
        font-weight: 700;
        color: #0f4c75;
        margin: 15px 0 10px 0;
        padding: 8px 10px;
        background-color: #f0f9ffff;
        border-left: 4px solid #c8e1eb;
        border-radius: 8px;
    }
    
    .rapport-doelstellingen-box {
        margin: 10px 0;
        padding: 10px;
        background: #ffffffff;
        border-left: 3px solid #ffffffff;
        font-size: 13px;
        line-height: 1.5;
        border-radius: 8px;
    }
    
    .rapport-grid-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin: 10px 0;
    }
    
    .rapport-evaluatie-box {
        margin: 10px 0;
        padding: 10px 12px;
        background: #fef9e6ff;
        border-left: 4px solid #f59e0b;
        font-size: 14px;
        line-height: 1.6;
        color: #78350f;
        border-radius: 8px;
    }
    
    .rapport-evaluatie-box strong {
        font-weight: 700;
        color: #92400e;
    }
</style>

<div class="rapport-algemene-info">
    <h3>📋 Algemene Informatie</h3>
    
    <table>
        <tr>
            <td>Testdatum</td>
            <td>{{ $inspanningstest->testdatum ? \Carbon\Carbon::parse($inspanningstest->testdatum)->format('d-m-Y') : '-' }}</td>
        </tr>
        <tr>
            <td>Testtype</td>
            <td>{{ $inspanningstest->testtype ?? '-' }}</td>
        </tr>
        <tr>
            <td>Naam</td>
            <td>{{ $klant->voornaam ?? '' }} {{ $klant->naam ?? '' }}</td>
        </tr>
        <tr>
            <td>Geboortedatum</td>
            <td>{{ $klant->geboortedatum ? \Carbon\Carbon::parse($klant->geboortedatum)->format('d-m-Y') : '-' }}</td>
        </tr>
        <tr>
            <td>Geslacht</td>
            <td>{{ $klant->geslacht ?? '-' }}</td>
        </tr>
        <tr>
            <td>Testlocatie</td>
            <td>{{ $inspanningstest->testlocatie ?? 'Bonami sportmedisch centrum' }}</td>
        </tr>
    </table>
    
    @if($inspanningstest->specifieke_doelstellingen)
    <h3>🎯 Specifieke Doelstellingen</h3>
    <div class="rapport-doelstellingen-box">
        {{ $inspanningstest->specifieke_doelstellingen }}
    </div>
    @endif
    
    <h3>⚖️ Lichaamssamenstelling & Fysiologie</h3>
    
    <div class="rapport-grid-2col">
        <table>
            <tr>
                <td>Lichaamslengte</td>
                <td>{{ $inspanningstest->lichaamslengte_cm ? $inspanningstest->lichaamslengte_cm . ' cm' : '-' }}</td>
            </tr>
            <tr>
                <td>Lichaamsgewicht</td>
                <td>{{ $inspanningstest->lichaamsgewicht_kg ? $inspanningstest->lichaamsgewicht_kg . ' kg' : '-' }}</td>
            </tr>
            <tr>
                <td>BMI</td>
                <td>
                    @if($inspanningstest->bmi)
                        {{ number_format($inspanningstest->bmi, 1) }}
                        @if($inspanningstest->bmi < 18.5)
                            <span style="color: #f59e0b;">(ondergewicht)</span>
                        @elseif($inspanningstest->bmi >= 18.5 && $inspanningstest->bmi < 25)
                            <span style="color: #10b981;">(normaal)</span>
                        @elseif($inspanningstest->bmi >= 25 && $inspanningstest->bmi < 30)
                            <span style="color: #f59e0b;">(overgewicht)</span>
                        @else
                            <span style="color: #ef4444;">(obesitas)</span>
                        @endif
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td>Vetpercentage</td>
                <td>{{ $inspanningstest->vetpercentage ? $inspanningstest->vetpercentage . ' %' : '-' }}</td>
            </tr>
        </table>
        
        <table>
            <tr>
                <td>Buikomtrek</td>
                <td>{{ $inspanningstest->buikomtrek_cm ? $inspanningstest->buikomtrek_cm . ' cm' : '-' }}</td>
            </tr>
            <tr>
                <td>Hartslag rust</td>
                <td>{{ $inspanningstest->hartslag_rust_bpm ? $inspanningstest->hartslag_rust_bpm . ' bpm' : '-' }}</td>
            </tr>
            <tr>
                <td>Hartslag max</td>
                <td>{{ $inspanningstest->maximale_hartslag_bpm ? $inspanningstest->maximale_hartslag_bpm . ' bpm' : '-' }}</td>
            </tr>
            <tr>
                <td>VO₂ max</td>
                <td>{{ $inspanningstest->vo2_max ? $inspanningstest->vo2_max . ' ml/kg/min' : '-' }}</td>
            </tr>
        </table>
    </div>
    
    @if($inspanningstest->besluit_lichaamssamenstelling)
    <div class="rapport-evaluatie-box">
        <strong>📊 Beoordeling:</strong> {{ $inspanningstest->besluit_lichaamssamenstelling }}
    </div>
    @else
    <div class="rapport-evaluatie-box">
        <strong>📊 Beoordeling:</strong> 
        @php
            $bmi = $inspanningstest->bmi;
            $vetperc = $inspanningstest->vetpercentage;
            $geslacht = $klant->geslacht ?? '';
            $evaluatie = '';
            
            // BMI beoordeling
            if ($bmi && $bmi >= 18.5 && $bmi < 25) {
                $evaluatie = 'BMI binnen gezonde grenzen. ';
            } elseif ($bmi && $bmi < 18.5) {
                $evaluatie = 'BMI duidt op ondergewicht - <em>werkpunt: geleidelijk gewicht aankomen voor betere prestaties</em>. ';
            } elseif ($bmi && $bmi >= 25 && $bmi < 30) {
                $evaluatie = 'BMI duidt op licht overgewicht - <em>werkpunt: gewichtsoptimalisatie kan prestaties verbeteren</em>. ';
            } elseif ($bmi && $bmi >= 30) {
                $evaluatie = 'BMI duidt op obesitas - <em>werkpunt: gewichtsverlies sterk aanbevolen voor gezondheid en prestaties</em>. ';
            }
            
            // Vetpercentage beoordeling met werkpunten
            if ($vetperc) {
                if ($geslacht === 'Man' || $geslacht === 'man' || $geslacht === 'M') {
                    if ($vetperc < 6) {
                        $evaluatie .= 'Vetpercentage zeer laag (elite atleet) - <em>let op voldoende voedingsinname voor herstel</em>.';
                    } elseif ($vetperc >= 6 && $vetperc < 14) {
                        $evaluatie .= 'Vetpercentage uitstekend voor competitieve sporters - <em>optimaal voor prestaties</em>.';
                    } elseif ($vetperc >= 14 && $vetperc < 18) {
                        $evaluatie .= 'Vetpercentage goed voor recreatieve sporters - <em>gezond en sportief</em>.';
                    } elseif ($vetperc >= 18 && $vetperc < 25) {
                        $evaluatie .= 'Vetpercentage gemiddeld - <em>werkpunt: verfijning voeding en training kan vetpercentage optimaliseren</em>.';
                    } else {
                        $evaluatie .= 'Vetpercentage verhoogd - <em>werkpunt: focus op vetverlies via gecombineerde kracht- en duurtraining + voedingsaanpassing</em>.';
                    }
                } else {
                    if ($vetperc < 14) {
                        $evaluatie .= 'Vetpercentage zeer laag (elite atlete) - <em>let op voldoende voedingsinname en hormonale balans</em>.';
                    } elseif ($vetperc >= 14 && $vetperc < 21) {
                        $evaluatie .= 'Vetpercentage uitstekend voor competitieve sporters - <em>optimaal voor prestaties</em>.';
                    } elseif ($vetperc >= 21 && $vetperc < 25) {
                        $evaluatie .= 'Vetpercentage goed voor recreatieve sporters - <em>gezond en sportief</em>.';
                    } elseif ($vetperc >= 25 && $vetperc < 32) {
                        $evaluatie .= 'Vetpercentage gemiddeld - <em>werkpunt: verfijning voeding en training kan vetpercentage optimaliseren</em>.';
                    } else {
                        $evaluatie .= 'Vetpercentage verhoogd - <em>werkpunt: focus op vetverlies via gecombineerde kracht- en duurtraining + voedingsaanpassing</em>.';
                    }
                }
            } else {
                // Alleen BMI beschikbaar
                if ($bmi && $bmi >= 18.5 && $bmi < 25 && !$evaluatie) {
                    $evaluatie = 'Lichaamssamenstelling binnen gezonde grenzen voor recreatieve sporters.';
                }
            }
            
            echo $evaluatie ?: 'Lichaamssamenstelling valt binnen normale waarden voor recreatieve sporters.';
        @endphp
    </div>
    @endif
</div>
