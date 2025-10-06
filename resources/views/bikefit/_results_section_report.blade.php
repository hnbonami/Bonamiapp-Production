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
        <table class="w-full text-sm mb-4 border-collapse border border-gray-300 rounded-lg overflow-hidden">
            <tbody>
                <tr class="bg-white"><td class="font-bold text-black border border-gray-300 px-3 py-2">A</td><td class="border border-gray-300 px-3 py-2">Zadelhoogte</td><td class="border border-gray-300 px-3 py-2 text-right">{{ $results['zadelhoogte'] ?? '' }} cm</td></tr>
                <tr class="bg-gray-50"><td class="font-bold text-black border border-gray-300 px-3 py-2">B</td><td class="border border-gray-300 px-3 py-2">Zadelterugstand</td><td class="border border-gray-300 px-3 py-2 text-right">{{ $results['zadelterugstand'] ?? '' }} cm</td></tr>
                <tr class="bg-white"><td class="font-bold text-black border border-gray-300 px-3 py-2">C</td><td class="border border-gray-300 px-3 py-2">Zadelterugstand (top zadel)</td><td class="border border-gray-300 px-3 py-2 text-right">{{ $results['zadelterugstand_top'] ?? '' }} cm</td></tr>
                <tr class="bg-gray-50"><td class="font-bold text-black border border-gray-300 px-3 py-2">D</td><td class="border border-gray-300 px-3 py-2">Horizontale reach</td><td class="border border-gray-300 px-3 py-2 text-right">{{ $results['reach'] ?? '' }} mm</td></tr>
                <tr class="bg-white"><td class="font-bold text-black border border-gray-300 px-3 py-2">E</td><td class="border border-gray-300 px-3 py-2">Reach</td><td class="border border-gray-300 px-3 py-2 text-right">{{ $results['directe_reach'] ?? '' }} mm</td></tr>
                <tr class="bg-gray-50"><td class="font-bold text-black border border-gray-300 px-3 py-2">F</td><td class="border border-gray-300 px-3 py-2">Drop</td><td class="border border-gray-300 px-3 py-2 text-right">{{ $results['drop'] ?? '' }} mm</td></tr>
                <tr class="bg-white"><td class="font-bold text-black border border-gray-300 px-3 py-2">G</td><td class="border border-gray-300 px-3 py-2">Cranklengte</td><td class="border border-gray-300 px-3 py-2 text-right">{{ $results['cranklengte'] ?? '' }} mm</td></tr>
                <tr class="bg-gray-50"><td class="font-bold text-black border border-gray-300 px-3 py-2">H</td><td class="border border-gray-300 px-3 py-2">Stuurbreedte</td><td class="border border-gray-300 px-3 py-2 text-right">{{ $results['stuurbreedte'] ?? '' }} mm</td></tr>
            </tbody>
        </table>
    </div>
</div>