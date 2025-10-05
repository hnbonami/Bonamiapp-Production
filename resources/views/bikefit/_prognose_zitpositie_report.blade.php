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
        <div style="flex: 1; min-width: 300px;">
            <img src="{{ $img }}" alt="Bikefit schema" style="width: 100%; max-width: 400px; height: auto;">
        </div>
        <div style="flex: 1; min-width: 300px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 16px;">
                <tbody>
                    <tr>
                        <td style="font-weight: bold; color: #2563eb; padding: 8px; border: 1px solid #e5e7eb;">A</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb;">Zadelhoogte</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">{{ $results['zadelhoogte'] ?? '' }} cm</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #2563eb; padding: 8px; border: 1px solid #e5e7eb;">B</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb;">Zadelterugstand</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">{{ $results['zadelterugstand'] ?? '' }} cm</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #2563eb; padding: 8px; border: 1px solid #e5e7eb;">C</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb;">Zadelterugstand (top zadel)</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">{{ $results['zadelterugstand_top'] ?? '' }} cm</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #2563eb; padding: 8px; border: 1px solid #e5e7eb;">D</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb;">Horizontale reach</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">{{ $results['reach'] ?? '' }} mm</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #2563eb; padding: 8px; border: 1px solid #e5e7eb;">E</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb;">Reach</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">{{ $results['directe_reach'] ?? '' }} mm</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #2563eb; padding: 8px; border: 1px solid #e5e7eb;">F</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb;">Drop</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">{{ $results['drop'] ?? '' }} mm</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #2563eb; padding: 8px; border: 1px solid #e5e7eb;">G</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb;">Cranklengte</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">{{ $results['cranklengte'] ?? '' }} mm</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; color: #2563eb; padding: 8px; border: 1px solid #e5e7eb;">H</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb;">Stuurbreedte</td>
                        <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">{{ $results['stuurbreedte'] ?? '' }} mm</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>