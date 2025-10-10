<div style="transform: scale(0.7); transform-origin: top left; margin-bottom: -100px;">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" style="max-width: 800px;">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" style="table-layout: fixed;">
                <thead class="bg-blue-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider" style="width: 33.33%;"></th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider" style="width: 33.33%;">Links</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider" style="width: 33.33%;">Rechts</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $mobilityTests = [
                            ['name' => 'Straight Leg Raise', 'subtitle' => '(hamstrings)', 'links' => $bikefit->straight_leg_raise_links ?? '-', 'rechts' => $bikefit->straight_leg_raise_rechts ?? '-'],
                            ['name' => 'Knieflexie', 'subtitle' => '(rectus femoris)', 'links' => $bikefit->knieflexie_links ?? '-', 'rechts' => $bikefit->knieflexie_rechts ?? '-'],
                            ['name' => 'Heup endorotatie', 'subtitle' => '', 'links' => $bikefit->heup_endorotatie_links ?? '-', 'rechts' => $bikefit->heup_endorotatie_rechts ?? '-'],
                            ['name' => 'Heup exorotatie', 'subtitle' => '', 'links' => $bikefit->heup_exorotatie_links ?? '-', 'rechts' => $bikefit->heup_exorotatie_rechts ?? '-'],
                            ['name' => 'Enkeldorsiflexie', 'subtitle' => '', 'links' => $bikefit->enkeldorsiflexie_links ?? '-', 'rechts' => $bikefit->enkeldorsiflexie_rechts ?? '-'],
                            ['name' => 'One leg squat', 'subtitle' => '', 'links' => $bikefit->one_leg_squat_links ?? '-', 'rechts' => $bikefit->one_leg_squat_rechts ?? '-'],
                        ];
                        $getColorClass = function($value) {
                            switch($value) {
                                case 'Heel laag': return 'bg-red-100 text-red-800';
                                case 'Laag': return 'bg-orange-100 text-orange-800';
                                case 'Gemiddeld': return 'bg-yellow-100 text-yellow-800';
                                case 'Hoog': return 'bg-green-100 text-green-800';
                                case 'Heel hoog': return 'bg-green-100 text-green-800';
                                default: return 'bg-gray-100 text-gray-600';
                            }
                        };
                    @endphp
                    @foreach($mobilityTests as $test)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $test['name'] }}</div>
                                @if(!empty($test['subtitle']))
                                    <div class="text-xs text-gray-500">{{ $test['subtitle'] }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $getColorClass($test['links']) }}">
                                    {{ $test['links'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $getColorClass($test['rechts']) }}">
                                    {{ $test['rechts'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>