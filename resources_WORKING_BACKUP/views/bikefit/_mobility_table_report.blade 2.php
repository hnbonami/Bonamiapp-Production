<style>
.mobility-bar {
    display: flex;
    width: 120px;
    height: 18px;
    border-radius: 6px;
    overflow: hidden;
    margin: 0 auto 4px auto;
    box-shadow: 0 1px 2px #ddd;
}
.mobility-bar-segment {
    flex: 1;
    height: 100%;
    position: relative;
}
.mobility-bar-segment.heel-laag { background: #ef4444 !important; }
.mobility-bar-segment.laag { background: #f59e42 !important; }
.mobility-bar-segment.gemiddeld { background: #fde047 !important; }
.mobility-bar-segment.hoog { background: #4ade80 !important; }
.mobility-bar-segment.heel-hoog { background: #16a34a !important; }
.mobility-bar-segment.selected::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    right: 2px;
    bottom: 2px;
    border: 2px solid #222;
    border-radius: 4px;
    pointer-events: none;
}
</style>
<div style="transform:scale(0.77); transform-origin:top center; width:fit-content; margin: 0 auto;">
    <table class="w-full text-sm" style="border:1px solid #e5e7eb;">
        <thead>
            <tr style="border-bottom:1px solid #e5e7eb;">
                <th class="text-left p-2" style="border-right:1px solid #e5e7eb;background:#c8e1eb;">Test</th>
                <th class="text-center p-2" style="background:#c8e1eb;">Links</th>
                <th class="text-center p-2" style="background:#c8e1eb;">Rechts</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tests = [
                    [
                        'label' => 'Straight Leg Raise (hamstrings)',
                        'key_links' => 'slr_links',
                        'key_rechts' => 'slr_rechts',
                        'desc' => 'De lengte van de hamstrings is meestal voldoende voor recreatief fietsen. Toch kan bij lange ritten of een sportieve positie spanning ontstaan in rug of bekken. Gerichte stretching of mobiliteitsoefeningen kunnen helpen dit risico te verkleinen.'
                    ],
                    [
                        'label' => 'Knieflexie (rectus femoris)',
                        'key_links' => 'knieflexie_links',
                        'key_rechts' => 'knieflexie_rechts',
                        'desc' => 'De spierlengte is meestal voldoende voor de meeste vormen van fietsen. Bij intensieve belasting kan er echter spanning of lichte kniepijn optreden. Gerichte stretching of mobiliteitsoefeningen kunnen helpen om dit te verbeteren.'
                    ],
                    [
                        'label' => 'Heup endorotatie',
                        'key_links' => 'heup_endorotatie_links',
                        'key_rechts' => 'heup_endorotatie_rechts',
                        'desc' => 'Beperkte endorotatie zorgt voor een mindere uitlijning van heup, knie en voet. Dit leidt vaak tot asymmetrisch trappen en extra belasting van rug of bekken. Extra mobiliteit kan helpen om dit te verbeteren.'
                    ],
                    [
                        'label' => 'Heup exorotatie',
                        'key_links' => 'heup_exorotatie_links',
                        'key_rechts' => 'heup_exorotatie_rechts',
                        'desc' => 'Voldoende bewegingsvrijheid voor recreatief fietsen. Bij intensieve belasting kan instabiliteit of kniepijn optreden. Mobiliteitsoefeningen kunnen dit risico doen dalen.'
                    ],
                    [
                        'label' => 'Enkeldorsiflexie',
                        'key_links' => 'enkeldorsiflexie_links',
                        'key_rechts' => 'enkeldorsiflexie_rechts',
                        'desc' => 'Beperkte dorsiflexie kan de trapbeweging verstoren en klachten veroorzaken. Extra mobiliteit kan helpen.'
                    ],
                    [
                        'label' => 'One leg squat',
                        'key_links' => 'one_leg_squat_links',
                        'key_rechts' => 'one_leg_squat_rechts',
                        'desc' => 'Test van kracht, stabiliteit en mobiliteit van het been. Zwakke score kan wijzen op instabiliteit of krachttekort.'
                    ],
                ];
                $scoreLabels = ['Heel laag', 'Laag', 'Gemiddeld', 'Hoog', 'Heel hoog'];
                $scoreColors = ['#ef4444', '#f59e42', '#fde047', '#4ade80', '#16a34a'];
                $getColorClass = function($value) {
                    switch($value) {
                        case 'Heel laag': return 'background-color:#fee2e2;color:#991b1b;';
                        case 'Laag': return 'background-color:#ffedd5;color:#9a3412;';
                        case 'Gemiddeld': return 'background-color:#fef9c3;color:#92400e;';
                        case 'Hoog': return 'background-color:#dcfce7;color:#166534;';
                        case 'Heel hoog': return 'background-color:#bbf7d0;color:#166534;';
                        default: return 'background-color:#e5e7eb;color:#374151;';
                    }
                };
            @endphp
            @foreach($tests as $index => $test)
                @php
                    $score_links = $mobiliteitklant[$test['key_links']] ?? '-';
                    $score_rechts = $mobiliteitklant[$test['key_rechts']] ?? '-';
                @endphp
                @if(in_array($score_links, ['_', '-', '']) || in_array($score_rechts, ['_', '-', '']))
                    @continue
                @endif
                <tr>
                    <td class="align-top p-2 font-semibold" style="border-right:1px solid #e5e7eb;">{{ $test['label'] }}</td>
                    @if($score_links === $score_rechts)
                        <td class="text-center align-top p-2" colspan="2">
                            <div class="mobility-bar" style="margin-left:auto;margin-right:auto;">
                                @foreach($scoreLabels as $i => $label)
                                    @php
                                        $colorClasses = ['heel-laag', 'laag', 'gemiddeld', 'hoog', 'heel-hoog'];
                                        $segmentClass = 'mobility-bar-segment ' . $colorClasses[$i];
                                        if($score_links === $label) {
                                            $segmentClass .= ' selected';
                                        }
                                    @endphp
                                    <div class="{{ $segmentClass }}"></div>
                                @endforeach
                            </div>
                            <span style="display:inline-block; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:600; {{ $getColorClass($score_links) }}">
                                {{ $score_links }}
                            </span>
                        </td>
                    @else
                        <td class="text-center align-top p-2">
                            <div class="mobility-bar">
                                @foreach($scoreLabels as $i => $label)
                                    @php
                                        $colorClasses = ['heel-laag', 'laag', 'gemiddeld', 'hoog', 'heel-hoog'];
                                        $segmentClass = 'mobility-bar-segment ' . $colorClasses[$i];
                                        if($score_links === $label) {
                                            $segmentClass .= ' selected';
                                        }
                                    @endphp
                                    <div class="{{ $segmentClass }}"></div>
                                @endforeach
                            </div>
                            <span style="display:inline-block; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:600; {{ $getColorClass($score_links) }}">
                                {{ $score_links }}
                            </span>
                        </td>
                        <td class="text-center align-top p-2">
                            <div class="mobility-bar">
                                @foreach($scoreLabels as $i => $label)
                                    @php
                                        $colorClasses = ['heel-laag', 'laag', 'gemiddeld', 'hoog', 'heel-hoog'];
                                        $segmentClass = 'mobility-bar-segment ' . $colorClasses[$i];
                                        if($score_rechts === $label) {
                                            $segmentClass .= ' selected';
                                        }
                                    @endphp
                                    <div class="{{ $segmentClass }}"></div>
                                @endforeach
                            </div>
                            <span style="display:inline-block; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:600; {{ $getColorClass($score_rechts) }}">
                                {{ $score_rechts }}
                            </span>
                        </td>
                    @endif
                </tr>
                @php
                    $desc_links = $test['desc_links'] ?? $test['desc'];
                    $desc_rechts = $test['desc_rechts'] ?? $test['desc'];
                    $score_links = $mobiliteitklant[$test['key_links']] ?? '-';
                    $score_rechts = $mobiliteitklant[$test['key_rechts']] ?? '-';
                @endphp
                @if($score_links === $score_rechts)
                    <tr>
                        <td style="border-right:1px solid #e5e7eb;"></td>
                        <td class="align-top p-2 text-xs text-gray-600 text-center" colspan="2">{{ $desc_links }}</td>
                    </tr>
                @else
                    <tr>
                        <td style="border-right:1px solid #e5e7eb;"></td>
                        <td class="align-top p-2 text-xs text-gray-600">{{ $desc_links }}</td>
                        <td class="align-top p-2 text-xs text-gray-600">{{ $desc_rechts }}</td>
                    </tr>
                @endif
                @if($index < count($tests) - 1)
                    <tr>
                        <td style="border-right:1px solid #e5e7eb;"></td>
                        <td colspan="2" style="border-top:1px solid #e5e7eb;"></td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
