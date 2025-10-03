@php
$mobilityOptions = [
    'Heel laag' => ['color' => '#e53935'],
    'Laag' => ['color' => '#fb8c00'],
    'Gemiddeld' => ['color' => '#fdd835'],
    'Hoog' => ['color' => '#aeea00'],
    'Heel hoog' => ['color' => '#43a047'],
];
$fields = [
    'straight_leg_raise' => [
        'label' => 'Straight Leg Raise (hamstrings)',
        'texts' => [
            'Heel laag' => 'Dit betekent dat de hamstrings sterk verkort zijn, waardoor de heup nauwelijks kan buigen. Hierdoor kantelt het bekken sneller naar achteren en krijgt de lage rug vaak extra belasting. Dit vergroot doorgaans de kans op rugklachten of een geforceerde fietshouding. Gerichte stretching of mobiliteitsoefeningen kunnen helpen dit risico te verkleinen.',
            'Laag' => 'De hamstrings zijn te strak en beperken de bewegingsvrijheid tijdens fietsen. Dit kan leiden tot stijfheid in de onderrug of ongemak in een sportieve houding. Klachten zoals hamstringvermoeidheid en spanning op de onderrug komen hier regelmatig voor. Gerichte stretching of mobiliteitsoefeningen kunnen helpen dit risico te verkleinen.',
            'Gemiddeld' => 'De lengte van de hamstrings is meestal voldoende voor recreatief fietsen. Toch kan bij lange ritten of een sportieve positie spanning ontstaan in rug of bekken. Gerichte stretching of mobiliteitsoefeningen kunnen helpen dit risico te verkleinen.',
            'Hoog' => 'De hamstringmobiliteit is goed, waardoor heup en rug doorgaans soepel samenwerken. Dit ondersteunt een efficiënte en comfortabele trapbeweging. Het risico op hamstring- of rugklachten is hierdoor vaak kleiner, al kunnen andere factoren meespelen.',
            'Heel hoog' => 'De hamstrings zijn zeer soepel, waardoor een sportieve fietshouding meestal goed vol te houden is. Rug en heupen blijven daardoor vaak vrij van spanning, zelfs bij langere of intensieve ritten. Dit bevordert doorgaans een vloeiende en efficiënte trapbeweging. Het risico op hamstring- of rugklachten is hierdoor vaak kleiner, al kunnen andere factoren meespelen.'
        ]
    ],
    'knieflexie' => [
        'label' => 'Knieflexie (rectus femoris)',
        'texts' => [
            'Heel laag' => 'De quadriceps, vooral de rectus femoris, zijn sterk verkort en beperken de knieflexie. Dit kan de trapbeweging duidelijk beïnvloeden en de belasting op de knie verhogen. Fietsers met dit profiel hebben vaak een grotere kans op voorste kniepijn of spanning in de heup. Gerichte stretching of mobiliteitsoefeningen kunnen helpen om dit te verbeteren.',
            'Laag' => 'De spierlengte is beperkt, waardoor de trapbeweging niet volledig efficiënt verloopt. Dit kan leiden tot stijfheid of ongemak na langere ritten. De kans op knieproblemen of spanning in de heup is hierdoor groter. Gerichte stretching of mobiliteitsoefeningen kunnen helpen om dit te verbeteren.',
            'Gemiddeld' => 'De spierlengte is meestal voldoende voor de meeste vormen van fietsen. Bij intensieve belasting kan er echter spanning of lichte kniepijn optreden. Gerichte stretching of mobiliteitsoefeningen kunnen helpen om dit te verbeteren.',
            'Hoog' => 'De rectus femoris heeft een goede lengte en laat doorgaans een soepele trapbeweging toe. Dit vermindert vaak de kans op knie- en heupklachten tijdens het fietsen.',
            'Heel hoog' => 'De spierlengte is uitstekend en geeft ruime bewegingsvrijheid in knie en heup. Dit ondersteunt doorgaans een krachtige en pijnvrije trapbeweging, zelfs bij hoge belasting. Het risico op spier- en gewrichtsklachten is daardoor meestal kleiner.'
        ]
    ],
    'heup_endorotatie' => [
        'label' => 'Heup endorotatie',
        'texts' => [
            'Heel laag' => 'Er is bijna geen endorotatie beschikbaar, wat zorgt voor een slechte uitlijning van heup, knie en voet. Dit leidt vaak tot asymmetrisch trappen en extra belasting van rug of bekken. De kans op klachten in knie of heup is hierdoor doorgaans hoog. Extra mobiliteit kan helpen om dit te verbeteren.',
            'Laag' => 'Er is beperkte endorotatie beschikbaar, wat zorgt voor een mindere uitlijning van heup, knie en voet. Dit leidt vaak tot asymmetrisch trappen en extra belasting van rug of bekken. De kans op klachten in knie of heup is hierdoor doorgaans hoog. Extra mobiliteit kan helpen om dit te verbeteren.',
            'Gemiddeld' => 'De endorotatie is voldoende voor de meeste fietsers. Toch kan bij hogere intensiteit of lange ritten lichte asymmetrie of spanning optreden. Extra mobiliteit kan helpen om dit te verbeteren.',
            'Hoog' => 'Er is voldoende bewegingsvrijheid om doorgaans symmetrisch en stabiel te trappen. Dit verlaagt vaak de kans op knie- en rugklachten. De fietshouding voelt hierdoor meestal natuurlijk en comfortabel aan.',
            'Heel hoog' => 'De heupen hebben een ruim bewegingsbereik, wat een optimale uitlijning en krachtverdeling ondersteunt. Dit maakt de trapbeweging doorgaans vloeiend en efficiënt. Het blessurerisico wordt hierdoor meestal sterk verkleind.'
        ]
    ],
    'heup_exorotatie' => [
        'label' => 'Heup exorotatie',
        'texts' => [
            'Heel laag' => 'De heup exorotatie is heel beperkt, waardoor de knie naar binnen kan vallen tijdens het trappen. Dit leidt vaak tot een onnatuurlijke beweging en verhoogt de kans op klachten. Stabiliteit en comfort zijn hierdoor meestal beperkt. Gerichte mobiliteitsoefeningen kunnen dit risico doen dalen.',
            'Laag' => 'De heup exorotatie is beperkt, waardoor de knie naar binnen kan vallen tijdens het trappen. Dit leidt vaak tot een onnatuurlijke beweging en verhoogt de kans op klachten. Stabiliteit en comfort zijn hierdoor meestal beperkt. Gerichte mobiliteitsoefeningen kunnen dit risico doen dalen.',
            'Gemiddeld' => 'Er is voldoende bewegingsvrijheid voor recreatief fietsen. Toch kan bij intensieve belasting of lange ritten instabiliteit of kniepijn optreden. Mobiliteitsoefeningen kunnen dit risico doen dalen.',
            'Hoog' => 'Er is voldoende bewegingsvrijheid om stabiel en krachtig te trappen. De knieën blijven meestal goed in lijn en de trapbeweging verloopt soepel. Het risico op klachten ter hoogte van de knie is hierdoor lager.',
            'Heel hoog' => 'Er is een uitstekend bewegingsbereik in de heupen. De knieën blijven meestal goed in lijn en de trapbeweging verloopt soepel. Het risico op klachten ter hoogte van de knie is hierdoor lager.'
        ]
    ],
    'enkeldorsiflexie' => [
        'label' => 'Enkeldorsiflexie',
        'texts' => [
            'Heel laag' => 'De enkeldorsiflexie is beperkt, waardoor de trapbeweging minder efficiënt verloopt. Knie- en kuitklachten komen hierdoor sneller voor bij langere of intensieve ritten. Het herstel kan hierdoor ook trager verlopen. Mobiliteitstraining kan helpen om dit risico te verminderen.',
            'Laag' => 'De enkeldorsiflexie is beperkt, waardoor de trapbeweging minder efficiënt verloopt. Knie- en kuitklachten komen hierdoor sneller voor bij langere of intensieve ritten. Het herstel kan hierdoor ook trager verlopen. Mobiliteitstraining kan helpen om dit risico te verminderen.',
            'Gemiddeld' => 'De enkeldorsiflexie is meestal voldoende voor recreatief fietsen. Bij hogere intensiteit kan er spanning of overbelasting optreden in knieën en kuiten. Mobiliteitstraining kan helpen om dit risico te verminderen.',
            'Hoog' => 'Er is voldoende bewegingsvrijheid in de enkel om soepel kracht over te brengen. Dit ondersteunt meestal een efficiënte traptechniek en helpt klachten te voorkomen. Knie- en kuitbelasting worden hierdoor vaak beter verdeeld.',
            'Heel hoog' => 'De enkledorsiflexie is uitstekend, waardoor er veel bewegingsvrijheid is. Dit ondersteunt meestal een efficiënte traptechniek en helpt klachten te voorkomen. Knie- en kuitbelasting worden hierdoor vaak beter verdeeld.'
        ]
    ],
    'one_leg_squat' => [
        'label' => 'One leg squat',
        'texts' => [
            'Heel laag' => 'Er is nauwelijks stabiliteit of controle; de knie zakt sterk naar binnen en het bekken kantelt weg. Dit wijst vaak op een verhoogt risico op knie- en heupblessures. Gerichte kracht- en stabiliteitstraining kan dit verbeteren.',
            'Laag' => 'De controle is onvoldoende, waardoor beperkte stabiliteit is bij de uitvoering. Dit vergroot de kans op overbelasting van knie of rug. Gerichte kracht- en stabiliteitstraining kan dit verbeteren.',
            'Gemiddeld' => 'De uitvoering is redelijk, met enkele kleine afwijkingen. Voor recreatief fietsen is dit meestal voldoende, maar bij hogere belasting kan dit leiden tot knie of rug klachten. Gerichte kracht- en stabiliteitstraining kan dit vaak verbeteren.',
            'Hoog' => 'De knie blijft stabiel boven de voet en het bekken blijft goed horizontaal. Dit ondersteunt een efficiënte en veilige krachtoverdracht. Het blessurerisico is hierdoor vaak lager.',
            'Heel hoog' => 'Er is uitstekende controle en kracht; de beweging verloopt stabiel en correct. Dit bevordert prestaties en helpt klachten te voorkomen. Het toont een sterke basis voor intensief en langdurig fietsen.'
        ]
    ],
];
@endphp
<div class="mt-8">
    <h3 class="text-lg font-bold mb-6">Functionele controle/ Mobiliteit</h3>
    <table class="w-full border border-gray-200 rounded-xl bg-white shadow-sm mb-8">
        <thead>
            <tr style="background:rgba(193,223,235,0.85);">
                <th class="py-4 px-4 text-left text-lg font-semibold">Test</th>
                <th class="py-4 px-4 text-center text-lg font-semibold">Links</th>
                <th class="py-4 px-4 text-center text-lg font-semibold">Rechts</th>
            </tr>
        </thead>
        <tbody>
        @foreach($fields as $key => $field)
            @php
                $scoreLinks = $bikefit->{$key.'_links'} ?? null;
                $scoreRechts = $bikefit->{$key.'_rechts'} ?? null;
            @endphp
            <tr class="align-top">
                <td class="py-4 px-4 font-semibold text-base align-top">{{ $field['label'] }}</td>
                @if($scoreLinks && $scoreRechts && $scoreLinks === $scoreRechts)
                    <td class="py-4 px-4 text-center align-top" colspan="2">
                        <div class="flex flex-col items-center justify-center">
                            <div class="scorebar mb-2" data-score="{{ $scoreLinks }}"></div>
                            <span class="text-sm font-bold mb-2" style="color:{{ $mobilityOptions[$scoreLinks]['color'] }};">{{ $scoreLinks }}</span>
                            <div class="px-4 py-2 text-sm text-gray-700 max-w-xl mx-auto">{{ $field['texts'][$scoreLinks] }}</div>
                        </div>
                    </td>
                @else
                    <td class="py-4 px-4 text-center align-top">
                        @if($scoreLinks)
                            <div class="flex flex-col items-center justify-center">
                                <div class="scorebar mb-2" data-score="{{ $scoreLinks }}"></div>
                                <span class="text-sm font-bold mb-2" style="color:{{ $mobilityOptions[$scoreLinks]['color'] }};">{{ $scoreLinks }}</span>
                                <div class="px-4 py-2 text-sm text-gray-700 max-w-xs mx-auto">{{ $field['texts'][$scoreLinks] }}</div>
                            </div>
                        @else
                            <span class="text-gray-400">Geen score</span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-center align-top">
                        @if($scoreRechts)
                            <div class="flex flex-col items-center justify-center">
                                <div class="scorebar mb-2" data-score="{{ $scoreRechts }}"></div>
                                <span class="text-sm font-bold mb-2" style="color:{{ $mobilityOptions[$scoreRechts]['color'] }};">{{ $scoreRechts }}</span>
                                <div class="px-4 py-2 text-sm text-gray-700 max-w-xs mx-auto">{{ $field['texts'][$scoreRechts] }}</div>
                            </div>
                        @else
                            <span class="text-gray-400">Geen score</span>
                        @endif
                    </td>
                @endif
            </tr>
                @if(!$loop->last)
                <tr>
                    <td colspan="3" class="p-0"><div style="height:1px;background-color:#e5e7eb;width:100%;"></div></td>
                </tr>
                @endif
        @endforeach
        </tbody>
    </table>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const colors = ['#43a047','#aeea00','#fdd835','#fb8c00','#e53935'];
        const labels = ['Heel hoog','Hoog','Gemiddeld','Laag','Heel laag'];
        document.querySelectorAll('.scorebar').forEach(function(bar) {
            const score = bar.getAttribute('data-score');
            let idx = labels.indexOf(score);
            if(idx === -1) idx = 2; // default: gemiddeld
            bar.innerHTML = '';
            bar.style.display = 'flex';
            bar.style.width = '180px';
            bar.style.height = '28px';
            bar.style.borderRadius = '14px';
            bar.style.overflow = 'hidden';
            bar.style.border = '2px solid #ddd';
            bar.style.boxShadow = '0 2px 8px rgba(0,0,0,0.07)';
            for(let i=0;i<5;i++) {
                let seg = document.createElement('div');
                seg.style.flex = '1';
                seg.style.height = '100%';
                seg.style.background = colors[i];
                seg.style.transition = 'box-shadow 0.3s';
                if(i === idx) {
                    seg.style.boxShadow = '0 0 12px 2px #333';
                    seg.style.borderRadius = '14px';
                }
                bar.appendChild(seg);
            }
        });
    });
</script>
