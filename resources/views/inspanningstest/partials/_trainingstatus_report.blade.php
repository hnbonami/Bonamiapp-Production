{{-- Trainingstatus bij Test - Rapport Versie (Print-ready) --}}
<style>
    .rapport-trainingstatus {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #1f2937;
        margin: 20px 0;
    }
    
    .rapport-trainingstatus h3 {
        font-size: 14px;
        font-weight: 700;
        color: #0f4c75;
        margin: 15px 0 10px 0;
        padding: 8px 10px;
        background-color: #c8e1eb;
        border-left: 4px solid #0f4c75;
    }
    
    .rapport-trainingstatus table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
    }
    
    .rapport-trainingstatus td {
        padding: 8px 10px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }
    
    .rapport-trainingstatus td:first-child {
        font-weight: 600;
        width: 40%;
        color: #374151;
    }
    
    .rapport-trainingstatus .score-balk {
        display: inline-block;
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        width: 200px;
        position: relative;
        vertical-align: middle;
        margin: 0 8px;
    }
    
    .rapport-trainingstatus .score-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s;
    }
    
    .rapport-trainingstatus .score-text {
        display: inline-block;
        font-weight: 600;
        font-size: 11px;
        min-width: 60px;
        vertical-align: middle;
    }
    
    .rapport-gemiddelde-score {
        margin: 15px 0;
        padding: 12px 15px;
        background: #f0f9ff;
        border-left: 4px solid #3b82f6;
        border-radius: 4px;
    }
    
    .rapport-gemiddelde-score strong {
        font-size: 13px;
        color: #1e40af;
        display: block;
        margin-bottom: 5px;
    }
    
    .rapport-gemiddelde-score .score-waarde {
        font-size: 18px;
        font-weight: 700;
        color: #3b82f6;
    }
    
    .rapport-advies-box {
        margin: 10px 0;
        padding: 10px 12px;
        background: #fff7ed;
        border-left: 4px solid #f59e0b;
        font-size: 10px;
        line-height: 1.5;
        color: #78350f;
    }
    
    .rapport-advies-box strong {
        font-weight: 700;
        color: #92400e;
    }
</style>

<div class="rapport-trainingstatus">
    <h3>💪 Trainingstatus bij Test</h3>
    
    <p style="margin: 5px 0 10px 0; font-size: 10px; color: #6b7280; font-style: italic;">
        Algemene conditie en herstelstatus op testmoment
    </p>
    
    <table>
        @php
            $slaap = $inspanningstest->slaapkwaliteit ?? null;
            $eetlust = $inspanningstest->eetlust ?? null;
            $gevoel = $inspanningstest->gevoel_op_training ?? null;
            $stress = $inspanningstest->stressniveau ?? null;
        @endphp
        
        <tr>
            <td>Slaapkwaliteit (0 = slecht, 10 = perfect)</td>
            <td>
                @if($slaap !== null)
                    <div class="score-balk">
                        <div class="score-fill" style="width: {{ $slaap * 10 }}%; background: {{ $slaap >= 7 ? '#10b981' : ($slaap >= 4 ? '#f59e0b' : '#ef4444') }};"></div>
                    </div>
                    <span class="score-text" style="color: {{ $slaap >= 7 ? '#10b981' : ($slaap >= 4 ? '#f59e0b' : '#ef4444') }};">
                        {{ $slaap }} / 10 ({{ $slaap >= 7 ? 'perfect' : ($slaap >= 4 ? 'matig' : 'slecht') }})
                    </span>
                @else
                    <span style="color: #9ca3af;">Niet ingevuld</span>
                @endif
            </td>
        </tr>
        
        <tr>
            <td>Eetlust (0 = slecht, 10 = perfect)</td>
            <td>
                @if($eetlust !== null)
                    <div class="score-balk">
                        <div class="score-fill" style="width: {{ $eetlust * 10 }}%; background: {{ $eetlust >= 7 ? '#10b981' : ($eetlust >= 4 ? '#f59e0b' : '#ef4444') }};"></div>
                    </div>
                    <span class="score-text" style="color: {{ $eetlust >= 7 ? '#10b981' : ($eetlust >= 4 ? '#f59e0b' : '#ef4444') }};">
                        {{ $eetlust }} / 10 ({{ $eetlust >= 7 ? 'perfect' : ($eetlust >= 4 ? 'matig' : 'slecht') }})
                    </span>
                @else
                    <span style="color: #9ca3af;">Niet ingevuld</span>
                @endif
            </td>
        </tr>
        
        <tr>
            <td>Gevoel op training (0 = slecht, 10 = perfect)</td>
            <td>
                @if($gevoel !== null)
                    <div class="score-balk">
                        <div class="score-fill" style="width: {{ $gevoel * 10 }}%; background: {{ $gevoel >= 7 ? '#10b981' : ($gevoel >= 4 ? '#f59e0b' : '#ef4444') }};"></div>
                    </div>
                    <span class="score-text" style="color: {{ $gevoel >= 7 ? '#10b981' : ($gevoel >= 4 ? '#f59e0b' : '#ef4444') }};">
                        {{ $gevoel }} / 10 ({{ $gevoel >= 7 ? 'perfect' : ($gevoel >= 4 ? 'matig' : 'slecht') }})
                    </span>
                @else
                    <span style="color: #9ca3af;">Niet ingevuld</span>
                @endif
            </td>
        </tr>
        
        <tr>
            <td>Stressniveau (0 = veel stress, 10 = geen stress)</td>
            <td>
                @if($stress !== null)
                    <div class="score-balk">
                        <div class="score-fill" style="width: {{ $stress * 10 }}%; background: {{ $stress >= 7 ? '#10b981' : ($stress >= 4 ? '#f59e0b' : '#ef4444') }};"></div>
                    </div>
                    <span class="score-text" style="color: {{ $stress >= 7 ? '#10b981' : ($stress >= 4 ? '#f59e0b' : '#ef4444') }};">
                        {{ $stress }} / 10 ({{ $stress >= 7 ? 'geen' : ($stress >= 4 ? 'matig' : 'veel') }})
                    </span>
                @else
                    <span style="color: #9ca3af;">Niet ingevuld</span>
                @endif
            </td>
        </tr>
    </table>
    
    @php
        // Bereken gemiddelde trainingstatus score
        $scores = array_filter([$slaap, $eetlust, $gevoel, $stress], fn($v) => $v !== null);
        $gemiddelde = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : null;
    @endphp
    
    @if($gemiddelde !== null)
    <div class="rapport-gemiddelde-score">
        <strong>Gemiddelde Trainingstatus Score</strong>
        <span class="score-waarde">{{ $gemiddelde }}</span> / 10
        <span style="color: {{ $gemiddelde >= 7 ? '#10b981' : ($gemiddelde >= 4 ? '#f59e0b' : '#ef4444') }}; margin-left: 10px;">
            ({{ $gemiddelde >= 7 ? 'Goed hersteld' : ($gemiddelde >= 4 ? 'Matig hersteld' : 'Slecht hersteld') }})
        </span>
    </div>
    
    <div class="rapport-advies-box">
        <strong>⚠️ Advies:</strong> 
        @if($gemiddelde >= 7)
            Uitstekende trainingstatus - lichaam is goed hersteld en klaar voor intensieve inspanning.
        @elseif($gemiddelde >= 4)
            Matige trainingstatus - <em>let op herstel en voeding, vermijd overtraining</em>.
        @else
            Lage trainingstatus - <em>focus op herstel en rust voor optimale testresultaten en vermijden overbelasting</em>.
        @endif
    </div>
    @endif
    
    @if($inspanningstest->training_dag_voor_test)
    <div style="margin: 10px 0; padding: 8px 10px; background: #f3f4f6; border-left: 3px solid #6b7280; font-size: 10px;">
        <strong>📅 Training 1 dag voor de test:</strong> {{ $inspanningstest->training_dag_voor_test }}
    </div>
    @endif
</div>