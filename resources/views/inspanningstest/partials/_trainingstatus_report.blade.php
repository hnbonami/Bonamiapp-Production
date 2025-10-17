{{-- Trainingstatus bij Test - Rapport Versie (Print-ready) --}}
<style>
    .rapport-trainingstatus {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 13px;
        line-height: 1.5;
        color: #1f2937;
        margin: 20px 0;
        width: 130%;
    }
    
    .rapport-trainingstatus h3 {
        font-size: 16px;
        font-weight: 700;
        color: #0f4c75;
        margin: 15px 0 10px 0;
        padding: 8px 10px;
        background-color: #f0f9ffff;
        border-left: 4px solid #c8e1eb;
        border-radius: 8px;
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
        font-size: 12px;
        margin-bottom: 8px;
        display: block;
    }
    
    .trainingstatus-labels {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: #6b7280;
        margin-top: 6px;
        padding: 0 2px;
    }
    
    .trainingstatus-score {
        text-align: center;
        font-weight: 700;
        font-size: 22px;
        margin-top: 8px;
        color: #1f2937;
    }
    
    .trainingstatus-slider {
        display: flex;
        width: 100%;
        height: 28px;
        border-radius: 14px;
        overflow: hidden;
        border: 2px solid #ddd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        margin: 10px 0;
    }
    
    .trainingstatus-slider-segment {
        flex: 1;
        height: 100%;
        transition: box-shadow 0.3s;
        min-width: 10px;
    }
    
    .seg-0, .seg-1, .seg-2 { background-color: #ef4444 !important; }
    .seg-3, .seg-4, .seg-5, .seg-6 { background-color: #f59e0b !important; }
    .seg-7, .seg-8, .seg-9 { background-color: #10b981 !important; }
    .seg-active { box-shadow: 0 0 12px 2px #333 !important; border-radius: 4px; }
    
    .rapport-gemiddelde-score {
        margin: 20px 0 15px 0;
        padding: 15px 20px;
        background: #f0f9ff;
        border-left: 4px solid #c8e1eb;
        border-radius: 8px;
        text-align: center;
    }
    
    .rapport-gemiddelde-score strong {
        font-size: 14px;
        color: #1e40af;
        display: block;
        margin-bottom: 8px;
    }
    
    .rapport-gemiddelde-score .score-groot {
        font-size: 38px;
        font-weight: 700;
        color: #3b82f6;
        line-height: 1;
    }
    
    .rapport-gemiddelde-score .score-label {
        font-size: 12px;
        color: #6b7280;
        margin-top: 8px;
        line-height: 1.5;
    }
    
    .rapport-advies-box {
        margin: 15px 0;
        padding: 12px 15px;
        background: #fff7ed;
        border-left: 4px solid #fff7ed;
        font-size: 13px;
        line-height: 1.6;
        color: #78350f;
        border-radius: 8px;
    }
    
    .rapport-advies-box strong {
        font-weight: 700;
        color: #92400e;
    }
    
    .training-info-box {
        margin: 10px 0;
        padding: 10px 12px;
        background: #f9fafb;
        border-left: 3px solid #f9fafb;
        font-size: 12px;
        color: #374151;
        border-radius: 8px;
    }
</style>

<div class="rapport-trainingstatus">
    <h3>💪 Trainingstatus bij Test</h3>
    
    <p style="margin: 5px 0 15px 0; font-size: 12px; color: #6b7280; font-style: italic;">
        Algemene conditie en herstelstatus op testmoment
    </p>
    
    @php
        $slaap = $inspanningstest->slaapkwaliteit ?? null;
        $eetlust = $inspanningstest->eetlust ?? null;
        $gevoel = $inspanningstest->gevoel_op_training ?? null;
        $stress = $inspanningstest->stressniveau ?? null;
    @endphp
    
    @php
        // Kleurenschema voor de sliders (0-10)
        $sliderColors = ['#ef4444', '#ef4444', '#ef4444', '#f59e0b', '#f59e0b', '#f59e0b', '#f59e0b', '#10b981', '#10b981', '#10b981'];
        
        // Helper functie om actief segment te bepalen
        function getActiveSegment($score) {
            if ($score === null) return -1;
            $scoreInt = (int)$score;
            return $scoreInt >= 10 ? 9 : $scoreInt;
        }
    @endphp
    
    <div class="trainingstatus-grid">
        {{-- Slaapkwaliteit --}}
        <div class="trainingstatus-item">
            <span class="trainingstatus-label">Slaapkwaliteit</span>
            @if($slaap !== null)
                @php $activeSegment = getActiveSegment($slaap); @endphp
                <div style="display: flex; justify-content: center; margin: 10px 0;">
                    <div class="trainingstatus-slider">
                        <div class="trainingstatus-slider-segment seg-0 {{ $activeSegment === 0 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-1 {{ $activeSegment === 1 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-2 {{ $activeSegment === 2 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-3 {{ $activeSegment === 3 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-4 {{ $activeSegment === 4 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-5 {{ $activeSegment === 5 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-6 {{ $activeSegment === 6 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-7 {{ $activeSegment === 7 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-8 {{ $activeSegment === 8 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-9 {{ $activeSegment === 9 ? 'seg-active' : '' }}"></div>
                    </div>
                </div>
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
                @php $activeSegment = getActiveSegment($eetlust); @endphp
                <div style="display: flex; justify-content: center; margin: 10px 0;">
                    <div class="trainingstatus-slider">
                        <div class="trainingstatus-slider-segment seg-0 {{ $activeSegment === 0 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-1 {{ $activeSegment === 1 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-2 {{ $activeSegment === 2 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-3 {{ $activeSegment === 3 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-4 {{ $activeSegment === 4 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-5 {{ $activeSegment === 5 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-6 {{ $activeSegment === 6 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-7 {{ $activeSegment === 7 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-8 {{ $activeSegment === 8 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-9 {{ $activeSegment === 9 ? 'seg-active' : '' }}"></div>
                    </div>
                </div>
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
                @php $activeSegment = getActiveSegment($gevoel); @endphp
                <div style="display: flex; justify-content: center; margin: 10px 0;">
                    <div class="trainingstatus-slider">
                        <div class="trainingstatus-slider-segment seg-0 {{ $activeSegment === 0 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-1 {{ $activeSegment === 1 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-2 {{ $activeSegment === 2 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-3 {{ $activeSegment === 3 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-4 {{ $activeSegment === 4 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-5 {{ $activeSegment === 5 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-6 {{ $activeSegment === 6 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-7 {{ $activeSegment === 7 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-8 {{ $activeSegment === 8 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-9 {{ $activeSegment === 9 ? 'seg-active' : '' }}"></div>
                    </div>
                </div>
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
                @php $activeSegment = getActiveSegment($stress); @endphp
                <div style="display: flex; justify-content: center; margin: 10px 0;">
                    <div class="trainingstatus-slider">
                        <div class="trainingstatus-slider-segment seg-0 {{ $activeSegment === 0 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-1 {{ $activeSegment === 1 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-2 {{ $activeSegment === 2 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-3 {{ $activeSegment === 3 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-4 {{ $activeSegment === 4 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-5 {{ $activeSegment === 5 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-6 {{ $activeSegment === 6 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-7 {{ $activeSegment === 7 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-8 {{ $activeSegment === 8 ? 'seg-active' : '' }}"></div>
                        <div class="trainingstatus-slider-segment seg-9 {{ $activeSegment === 9 ? 'seg-active' : '' }}"></div>
                    </div>
                </div>
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