{{-- Trainingstatus bij Test - Rapport Versie (Print-ready) --}}
<style>
    .rapport-trainingstatus {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 11px;
        line-height: 1.4;
        color: #1f2937;
        margin: 20px 0;
        width: 120%;
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
    
    .trainingstatus-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin: 15px 0;
        width: 100%;
    }
    
    .trainingstatus-item {
        margin-bottom: 15px;
        width: 100%;
    }
    
    .trainingstatus-label {
        font-weight: 600;
        color: #374151;
        font-size: 10px;
        margin-bottom: 8px;
        display: block;
    }
    
    .trainingstatus-labels {
        display: flex;
        justify-content: space-between;
        font-size: 9px;
        color: #6b7280;
        margin-top: 6px;
        padding: 0 2px;
    }
    
    .trainingstatus-score {
        text-align: center;
        font-weight: 700;
        font-size: 20px;
        margin-top: 8px;
        color: #1f2937;
    }
    
    .rapport-gemiddelde-score {
        margin: 20px 0 15px 0;
        padding: 15px 20px;
        background: #f0f9ff;
        border-left: 4px solid #3b82f6;
        border-radius: 4px;
        text-align: center;
    }
    
    .rapport-gemiddelde-score strong {
        font-size: 12px;
        color: #1e40af;
        display: block;
        margin-bottom: 8px;
    }
    
    .rapport-gemiddelde-score .score-groot {
        font-size: 36px;
        font-weight: 700;
        color: #3b82f6;
        line-height: 1;
    }
    
    .rapport-gemiddelde-score .score-label {
        font-size: 10px;
        color: #6b7280;
        margin-top: 8px;
        line-height: 1.5;
    }
    
    .rapport-advies-box {
        margin: 15px 0;
        padding: 12px 15px;
        background: #fff7ed;
        border-left: 4px solid #f59e0b;
        font-size: 11px;
        line-height: 1.6;
        color: #78350f;
    }
    
    .rapport-advies-box strong {
        font-weight: 700;
        color: #92400e;
    }
    
    .training-info-box {
        margin: 10px 0;
        padding: 10px 12px;
        background: #f9fafb;
        border-left: 3px solid #6b7280;
        font-size: 10px;
        color: #374151;
    }
</style>

<div class="rapport-trainingstatus">
    <h3>💪 Trainingstatus bij Test</h3>
    
    <p style="margin: 5px 0 15px 0; font-size: 10px; color: #6b7280; font-style: italic;">
        Algemene conditie en herstelstatus op testmoment
    </p>
    
    @php
        $slaap = $inspanningstest->slaapkwaliteit ?? null;
        $eetlust = $inspanningstest->eetlust ?? null;
        $gevoel = $inspanningstest->gevoel_op_training ?? null;
        $stress = $inspanningstest->stressniveau ?? null;
    @endphp
    
    <div class="trainingstatus-grid">
        {{-- Slaapkwaliteit --}}
        <div class="trainingstatus-item">
            <span class="trainingstatus-label">Slaapkwaliteit</span>
            @if($slaap !== null)
                <svg width="100%" height="50" style="margin: 10px 0;">
                    <rect x="0" y="5" width="33.33%" height="40" fill="#ef4444" rx="20" ry="20"/>
                    <rect x="33.33%" y="5" width="33.34%" height="40" fill="#f59e0b"/>
                    <rect x="66.67%" y="5" width="33.33%" height="40" fill="#10b981" rx="20" ry="20"/>
                    <rect x="{{ $slaap * 10 }}%" y="0" width="4" height="50" fill="#1f2937"/>
                </svg>
                <div class="trainingstatus-labels">
                    <span>0 (slecht)</span>
                    <span>10 (perfect)</span>
                </div>
                <div class="trainingstatus-score">{{ $slaap }} / 10</div>
            @else
                <div style="color: #9ca3af; font-style: italic; margin-top: 10px;">Niet ingevuld</div>
            @endif
        </div>
        
        {{-- Eetlust --}}
        <div class="trainingstatus-item">
            <span class="trainingstatus-label">Eetlust</span>
            @if($eetlust !== null)
                <svg width="100%" height="50" style="margin: 10px 0;">
                    <rect x="0" y="5" width="33.33%" height="40" fill="#ef4444" rx="20" ry="20"/>
                    <rect x="33.33%" y="5" width="33.34%" height="40" fill="#f59e0b"/>
                    <rect x="66.67%" y="5" width="33.33%" height="40" fill="#10b981" rx="20" ry="20"/>
                    <rect x="{{ $eetlust * 10 }}%" y="0" width="4" height="50" fill="#1f2937"/>
                </svg>
                <div class="trainingstatus-labels">
                    <span>0 (slecht)</span>
                    <span>10 (perfect)</span>
                </div>
                <div class="trainingstatus-score">{{ $eetlust }} / 10</div>
            @else
                <div style="color: #9ca3af; font-style: italic; margin-top: 10px;">Niet ingevuld</div>
            @endif
        </div>
        
        {{-- Gevoel op training --}}
        <div class="trainingstatus-item">
            <span class="trainingstatus-label">Gevoel op training</span>
            @if($gevoel !== null)
                <svg width="100%" height="50" style="margin: 10px 0;">
                    <rect x="0" y="5" width="33.33%" height="40" fill="#ef4444" rx="20" ry="20"/>
                    <rect x="33.33%" y="5" width="33.34%" height="40" fill="#f59e0b"/>
                    <rect x="66.67%" y="5" width="33.33%" height="40" fill="#10b981" rx="20" ry="20"/>
                    <rect x="{{ $gevoel * 10 }}%" y="0" width="4" height="50" fill="#1f2937"/>
                </svg>
                <div class="trainingstatus-labels">
                    <span>0 (slecht)</span>
                    <span>10 (perfect)</span>
                </div>
                <div class="trainingstatus-score">{{ $gevoel }} / 10</div>
            @else
                <div style="color: #9ca3af; font-style: italic; margin-top: 10px;">Niet ingevuld</div>
            @endif
        </div>
        
        {{-- Stressniveau --}}
        <div class="trainingstatus-item">
            <span class="trainingstatus-label">Stressniveau</span>
            @if($stress !== null)
                <svg width="100%" height="50" style="margin: 10px 0;">
                    <rect x="0" y="5" width="33.33%" height="40" fill="#ef4444" rx="20" ry="20"/>
                    <rect x="33.33%" y="5" width="33.34%" height="40" fill="#f59e0b"/>
                    <rect x="66.67%" y="5" width="33.33%" height="40" fill="#10b981" rx="20" ry="20"/>
                    <rect x="{{ $stress * 10 }}%" y="0" width="4" height="50" fill="#1f2937"/>
                </svg>
                <div class="trainingstatus-labels">
                    <span>0 (veel stress)</span>
                    <span>10 (geen)</span>
                </div>
                <div class="trainingstatus-score">{{ $stress }} / 10</div>
            @else
                <div style="color: #9ca3af; font-style: italic; margin-top: 10px;">Niet ingevuld</div>
            @endif
        </div>
    </div>
    
    @php
        // Bereken gemiddelde trainingstatus score
        $scores = array_filter([$slaap, $eetlust, $gevoel, $stress], fn($v) => $v !== null);
        $gemiddelde = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : null;
    @endphp
    
    @if($gemiddelde !== null)
    <div class="rapport-gemiddelde-score">
        <strong>Gemiddelde Trainingstatus Score</strong>
        <div class="score-groot">{{ $gemiddelde }}</div>
        <div class="score-label">
            Automatisch berekend gemiddelde van bovenstaande scores<br>
            <strong style="color: {{ $gemiddelde >= 7 ? '#10b981' : ($gemiddelde >= 4 ? '#f59e0b' : '#ef4444') }};">
                {{ $gemiddelde >= 7 ? '✓ Goed hersteld' : ($gemiddelde >= 4 ? '⚠ Matig hersteld' : '✗ Slecht hersteld') }}
            </strong>
        </div>
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
    <div class="training-info-box">
        <strong>📅 Training 1 dag voor de test:</strong> {{ $inspanningstest->training_dag_voor_test }}
    </div>
    @endif
</div>