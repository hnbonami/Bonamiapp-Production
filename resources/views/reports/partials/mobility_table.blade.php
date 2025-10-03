@php
    $tests = [
        ['name' => 'Straight Leg Raise', 'subtitle' => '(hamstrings)', 'left' => $bikefit->straight_leg_raise_links ?? '-', 'right' => $bikefit->straight_leg_raise_rechts ?? '-'],
        ['name' => 'Knieflexie', 'subtitle' => '(rectus femoris)', 'left' => $bikefit->knieflexie_links ?? '-', 'right' => $bikefit->knieflexie_rechts ?? '-'],
        ['name' => 'Heup endorotatie', 'subtitle' => '', 'left' => $bikefit->heup_endorotatie_links ?? '-', 'right' => $bikefit->heup_endorotatie_rechts ?? '-'],
        ['name' => 'Heup exorotatie', 'subtitle' => '', 'left' => $bikefit->heup_exorotatie_links ?? '-', 'right' => $bikefit->heup_exorotatie_rechts ?? '-'],
        ['name' => 'Enkeldorsiflexie', 'subtitle' => '', 'left' => $bikefit->enkeldorsiflexie_links ?? '-', 'right' => $bikefit->enkeldorsiflexie_rechts ?? '-'],
        ['name' => 'One leg squat', 'subtitle' => '', 'left' => $bikefit->one_leg_squat_links ?? '-', 'right' => $bikefit->one_leg_squat_rechts ?? '-'],
    ];
    $level = function($v){
        $map = ['heel laag'=>1,'laag'=>2,'gemiddeld'=>3,'hoog'=>4,'heel hoog'=>5];
        $k = strtolower(trim((string)$v));
        return $map[$k] ?? 0;
    };
    $badgeColor = function($l){
        return [1=>'#f44336',2=>'#ff9800',3=>'#f6a300',4=>'#4caf50',5=>'#2e7d32'][$l] ?? '#9e9e9e';
    };
@endphp

<div style="display:inline-block; transform: scale(0.7); transform-origin: top left;">
    <table style="width:100%; border-collapse:collapse; font-family:inherit;">
        <thead>
            <tr>
                <th style="text-align:left; width:40%; padding:10px; font-size:13px; color:#111; border-bottom:1px solid #e5e7eb;">Test</th>
                <th style="text-align:center; width:30%; padding:10px; font-size:13px; color:#111; border-bottom:1px solid #e5e7eb;">Links</th>
                <th style="text-align:center; width:30%; padding:10px; font-size:13px; color:#111; border-bottom:1px solid #e5e7eb;">Rechts</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tests as $t)
                @php $L=$level($t['left']); $R=$level($t['right']); $Lx= $L? (10 + ($L-1)*20):0; $Rx= $R? (10 + ($R-1)*20):0; @endphp
                <tr>
                    <td style="padding:14px 10px; border-top:1px solid #e5e7eb;">
                        <div style="font-weight:600; color:#111827; font-size:14px;">{{ $t['name'] }}</div>
                        @if(!empty($t['subtitle']))<div style="color:#6b7280; font-size:12px;">{{ $t['subtitle'] }}</div>@endif
                    </td>
                    <td style="padding:14px 10px; border-top:1px solid #e5e7eb; text-align:center;">
                        <div style="position:relative; display:inline-block; width:160px; height:16px; border-radius:999px; background: linear-gradient(90deg,#2e7d32 0%, #2e7d32 20%, #4caf50 20%, #4caf50 40%, #f6a300 40%, #f6a300 60%, #ff9800 60%, #ff9800 80%, #f44336 80%, #f44336 100%); box-shadow: inset 0 0 0 1px rgba(0,0,0,.06);">
                            @if($L>0)
                                <span style="position:absolute; top:50%; left: {{ $Lx }}%; width:20px; height:20px; background:#fff; border:1px solid #d1d5db; border-radius:50%; transform: translate(-50%, -50%); box-shadow: 0 1px 2px rgba(0,0,0,.08);"></span>
                            @endif
                        </div>
                        <div style="display:inline-block; margin-top:6px; padding:3px 8px; border-radius:999px; font-size:12px; font-weight:600; color:#fff; background: {{ $badgeColor($L) }};">{{ $t['left'] }}</div>
                    </td>
                    <td style="padding:14px 10px; border-top:1px solid #e5e7eb; text-align:center;">
                        <div style="position:relative; display:inline-block; width:160px; height:16px; border-radius:999px; background: linear-gradient(90deg,#2e7d32 0%, #2e7d32 20%, #4caf50 20%, #4caf50 40%, #f6a300 40%, #f6a300 60%, #ff9800 60%, #ff9800 80%, #f44336 80%, #f44336 100%); box-shadow: inset 0 0 0 1px rgba(0,0,0,.06);">
                            @if($R>0)
                                <span style="position:absolute; top:50%; left: {{ $Rx }}%; width:20px; height:20px; background:#fff; border:1px solid #d1d5db; border-radius:50%; transform: translate(-50%, -50%); box-shadow: 0 1px 2px rgba(0,0,0,.08);"></span>
                            @endif
                        </div>
                        <div style="display:inline-block; margin-top:6px; padding:3px 8px; border-radius:999px; font-size:12px; font-weight:600; color:#fff; background: {{ $badgeColor($R) }};">{{ $t['right'] }}</div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
