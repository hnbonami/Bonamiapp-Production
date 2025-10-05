<div class="bg-white rounded shadow p-8 mb-8">
    <h2 class="text-xl font-bold text-center mb-6">📐 Zitpositie voor aanpassingen</h2>
    <div class="flex flex-col md:flex-row gap-8 items-center">
        @php
            $type = strtolower(trim($bikefit->type_fitting ?? ''));
            if (in_array($type, ['mtb', 'mountainbike'])) {
                $img = '/images/bikefit-schema-mtb.png';
            } elseif (in_array($type, ['tijdritfiets', 'tt'])) {
                $img = '/images/bikefit-schema-tt.png';
            } else {
                $img = '/images/bikefit-schema.png';
            }
        @endphp
        <img src="{{ $img }}" alt="Bikefit schema" class="w-full md:w-1/2 max-w-md mx-auto">
        <div class="w-full md:w-1/2">
            <table class="w-full text-sm mb-4">
                <tbody>
                    <tr><td class="font-bold text-blue-700">A</td><td>Zadelhoogte</td><td>{{ $results['zadelhoogte'] ?? '' }} cm</td></tr>
                    <tr><td class="font-bold text-blue-700">B</td><td>Zadelterugstand</td><td>{{ $results['zadelterugstand'] ?? '' }} cm</td></tr>
                    <tr><td class="font-bold text-blue-700">C</td><td>Zadelterugstand (top zadel)</td><td>{{ $results['zadelterugstand_top'] ?? '' }} cm</td></tr>
                    <tr><td class="font-bold text-blue-700">D</td><td>Horizontale reach</td><td>{{ $results['reach'] ?? '' }} mm</td></tr>
                    <tr><td class="font-bold text-blue-700">E</td><td>Reach</td><td>{{ $results['directe_reach'] ?? '' }} mm</td></tr>
                    <tr><td class="font-bold text-blue-700">F</td><td>Drop</td><td>{{ $results['drop'] ?? '' }} mm</td></tr>
                    <tr><td class="font-bold text-blue-700">G</td><td>Cranklengte</td><td>{{ $results['cranklengte'] ?? '' }} mm</td></tr>
                    <tr><td class="font-bold text-blue-700">H</td><td>Stuurbreedte</td><td>{{ $results['stuurbreedte'] ?? '' }} mm</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>