@if(isset($resultsNa))
            @endif
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
<style>
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>
<div class="flex flex-col items-center" style="width: 500px; margin: 0 auto; min-height: 500px; position: relative;">
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
    
    <!-- Afbeelding bovenaan -->
    <div class="mb-4" style="width: 320px; height: auto; flex-shrink: 0;">
        <img src="{{ $img }}" alt="Bikefit schema" style="width: 320px; height: auto; display: block; margin: 0 auto;">
    </div>
    
    <!-- Tabel eronder met zwart randje en bewerkbare velden -->
    <div style="width: 500px; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); background-color: white; flex-shrink: 0;">
        <table style="width: 100%; font-size: 14px; border-collapse: collapse; table-layout: fixed;">
            <tbody>
                <tr class="bg-white">
                    <td class="font-bold text-black border-b border-gray-300 px-2 py-1 w-8 text-center">A</td>
                    <td class="border-b border-gray-300 px-2 py-1 w-40">Zadelhoogte</td>
                    <td class="border-b border-gray-300 px-2 py-1">
                        <div class="flex items-center justify-between">
                            <span></span>
                            <div class="flex items-center">
                                <input type="number" step="0.1" name="zadelhoogte" value="{{ $results['zadelhoogte'] ?? '' }}" class="px-2 py-1 w-16 text-right bg-transparent border-0 outline-none focus:bg-gray-50 focus:border focus:border-gray-300 rounded text-sm font-medium" form="bikefit-form">
                                <span class="ml-2 text-sm font-medium">cm</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="bg-gray-50">
                    <td class="font-bold text-black border-b border-gray-300 px-2 py-1">B</td>
                    <td class="border-b border-gray-300 px-2 py-1">Zadelterugstand</td>
                    <td class="border-b border-gray-300 px-2 py-1">
                        <div class="flex items-center justify-between">
                            <span></span>
                            <div class="flex items-center">
                                <input type="number" step="0.1" name="zadelterugstand" value="{{ $results['zadelterugstand'] ?? '' }}" class="px-2 py-1 w-16 text-right bg-transparent border-0 outline-none focus:bg-gray-50 focus:border focus:border-gray-300 rounded text-sm font-medium" form="bikefit-form">
                                <span class="ml-2 text-sm font-medium">cm</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="bg-white">
                    <td class="font-bold text-black border-b border-gray-300 px-2 py-1">C</td>
                    <td class="border-b border-gray-300 px-2 py-1">Zadelterugstand (top zadel)</td>
                    <td class="border-b border-gray-300 px-2 py-1">
                        <div class="flex items-center justify-between">
                            <span></span>
                            <div class="flex items-center">
                                <input type="number" step="0.1" name="zadelterugstand_top" value="{{ $results['zadelterugstand_top'] ?? '' }}" class="px-2 py-1 w-16 text-right bg-transparent border-0 outline-none focus:bg-gray-50 focus:border focus:border-gray-300 rounded text-sm font-medium" form="bikefit-form">
                                <span class="ml-2 text-sm font-medium">cm</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="bg-gray-50">
                    <td class="font-bold text-black border-b border-gray-300 px-2 py-1">D</td>
                    <td class="border-b border-gray-300 px-2 py-1">Horizontale reach</td>
                    <td class="border-b border-gray-300 px-2 py-1">
                        <div class="flex items-center justify-between">
                            <span></span>
                            <div class="flex items-center">
                                <input type="number" step="0.1" name="horizontale_reach" value="{{ $results['horizontale_reach'] ?? '' }}" class="px-2 py-1 w-16 text-right bg-transparent border-0 outline-none focus:bg-gray-50 focus:border focus:border-gray-300 rounded text-sm font-medium" form="bikefit-form">
                                <span class="ml-2 text-sm font-medium">mm</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="bg-white">
                    <td class="font-bold text-black border-b border-gray-300 px-2 py-1">E</td>
                    <td class="border-b border-gray-300 px-2 py-1">Reach</td>
                    <td class="border-b border-gray-300 px-2 py-1">
                        <div class="flex items-center justify-between">
                            <span></span>
                            <div class="flex items-center">
                                <input type="number" step="0.1" name="reach" value="{{ $results['reach'] ?? '' }}" class="px-2 py-1 w-16 text-right bg-transparent border-0 outline-none focus:bg-gray-50 focus:border focus:border-gray-300 rounded text-sm font-medium" form="bikefit-form">
                                <span class="ml-2 text-sm font-medium">mm</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="bg-gray-50">
                    <td class="font-bold text-black border-b border-gray-300 px-2 py-1">F</td>
                    <td class="border-b border-gray-300 px-2 py-1">Drop</td>
                    <td class="border-b border-gray-300 px-2 py-1">
                        <div class="flex items-center justify-between">
                            <span></span>
                            <div class="flex items-center">
                                <input type="number" step="0.1" name="drop" value="{{ $results['drop'] ?? '' }}" class="px-2 py-1 w-16 text-right bg-transparent border-0 outline-none focus:bg-gray-50 focus:border focus:border-gray-300 rounded text-sm font-medium" form="bikefit-form">
                                <span class="ml-2 text-sm font-medium">mm</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="bg-white">
                    <td class="font-bold text-black border-b border-gray-300 px-2 py-1">G</td>
                    <td class="border-b border-gray-300 px-2 py-1">Cranklengte</td>
                    <td class="border-b border-gray-300 px-2 py-1">
                        <div class="flex items-center justify-between">
                            <span></span>
                            <div class="flex items-center">
                                <input type="number" step="0.1" name="cranklengte" value="{{ $results['cranklengte'] ?? '' }}" class="px-2 py-1 w-16 text-right bg-transparent border-0 outline-none focus:bg-gray-50 focus:border focus:border-gray-300 rounded text-sm font-medium" form="bikefit-form">
                                <span class="ml-2 text-sm font-medium">mm</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="bg-gray-50">
                    <td class="font-bold text-black px-2 py-1">H</td>
                    <td class="px-2 py-1">Stuurbreedte</td>
                    <td class="px-2 py-1">
                        <div class="flex items-center justify-between">
                            <span></span>
                            <div class="flex items-center">
                                <input type="number" step="0.1" name="stuurbreedte" value="{{ $results['stuurbreedte'] ?? '' }}" class="px-2 py-1 w-16 text-right bg-transparent border-0 outline-none focus:bg-gray-50 focus:border focus:border-gray-300 rounded text-sm font-medium" form="bikefit-form">
                                <span class="ml-2 text-sm font-medium">mm</span>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
