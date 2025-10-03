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
<div style="transform:scale(0.85); transform-origin:top left; width:fit-content; margin-left:60px;">
<div class="flex flex-col items-center gap-4">
    <img src="{{ $img }}" alt="Bikefit schema" class="w-full max-w-md mx-auto mb-2">
    <div class="w-full max-w-xs">
        <table class="w-full text-sm mb-4">
            <tbody>
            <tr>
                <td class="font-bold" style="color:#c8e1eb;">A</td>
                <td>Zadelhoogte</td>
                <td>
                    <input type="number" step="0.1" name="zadelhoogte" value="{{ $results['zadelhoogte'] ?? '' }}" class="px-1 py-1 w-16 text-right" style="border:none; outline:none; appearance:none; -webkit-appearance:none; -moz-appearance:textfield;" form="bikefit-report-form"> cm
                </td>
            </tr>
            <tr>
                <td class="font-bold" style="color:#c8e1eb;">B</td>
                <td>Zadelterugstand</td>
                <td>
                    <input type="number" step="0.1" name="zadelterugstand" value="{{ $results['zadelterugstand'] ?? '' }}" class="px-1 py-1 w-16 text-right" style="border:none; outline:none; appearance:none; -webkit-appearance:none; -moz-appearance:textfield;" form="bikefit-report-form"> cm
                </td>
            </tr>
            <tr>
                <td class="font-bold" style="color:#c8e1eb;">C</td>
                <td>Zadelterugstand (top zadel)</td>
                <td>
                    <input type="number" step="0.1" name="zadelterugstand_top" value="{{ $results['zadelterugstand_top'] ?? '' }}" class="px-1 py-1 w-16 text-right" style="border:none; outline:none; appearance:none; -webkit-appearance:none; -moz-appearance:textfield;" form="bikefit-report-form"> cm
                </td>
            </tr>
            <tr>
                <td class="font-bold" style="color:#c8e1eb;">D</td>
                <td>Horizontale reach</td>
                <td>
                    <input type="number" step="0.1" name="reach" value="{{ $results['reach'] ?? '' }}" class="px-1 py-1 w-16 text-right" style="border:none; outline:none; appearance:none; -webkit-appearance:none; -moz-appearance:textfield;" form="bikefit-report-form"> cm
                </td>
            </tr>
            <tr>
                <td class="font-bold" style="color:#c8e1eb;">E</td>
                <td>Reach</td>
                <td>
                    <input type="number" step="0.1" name="reach_e" value="{{ $results['reach_e'] ?? '' }}" class="px-1 py-1 w-16 text-right" style="border:none; outline:none; appearance:none; -webkit-appearance:none; -moz-appearance:textfield;" form="bikefit-report-form"> cm
                </td>
            </tr>
            <tr>
                <td class="font-bold" style="color:#c8e1eb;">F</td>
                <td>Drop</td>
                <td>
                    <input type="number" step="0.1" name="drop_zadel_stuur" value="{{ $results['drop_zadel_stuur'] ?? '' }}" class="px-1 py-1 w-16 text-right" style="border:none; outline:none; appearance:none; -webkit-appearance:none; -moz-appearance:textfield;" form="bikefit-report-form"> cm
                </td>
            </tr>
            <tr>
                <td class="font-bold" style="color:#c8e1eb;">G</td>
                <td>Cranklengte</td>
                <td>
                    <input type="number" step="0.1" name="cranklengte" value="{{ $results['cranklengte'] ?? '' }}" class="px-1 py-1 w-16 text-right" style="border:none; outline:none; appearance:none; -webkit-appearance:none; -moz-appearance:textfield;" form="bikefit-report-form"> mm
                </td>
            </tr>
            <tr>
                <td class="font-bold" style="color:#c8e1eb;">H</td>
                <td>Stuurbreedte</td>
                <td>
                    <input type="number" step="0.1" name="stuurbreedte" value="{{ $results['stuurbreedte'] ?? '' }}" class="px-1 py-1 w-16 text-right" style="border:none; outline:none; appearance:none; -webkit-appearance:none; -moz-appearance:textfield;" form="bikefit-report-form"> cm
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <!-- Mobiliteitstabel is verwijderd om dubbele weergave te voorkomen -->
</div>
</div>
