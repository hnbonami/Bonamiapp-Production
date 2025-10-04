@extends('layouts.app')

@section('content')
<div class="py-2">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="p-8">
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-6 mt-0">
                Testresultaten — {{ $klant->naam }}
            </h1>
        </div>
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                
                <!-- Test Informatie -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">Test Informatie</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="font-semibold">Testdatum:</span>
                            {{ \Carbon\Carbon::parse($test->testdatum)->format('d-m-Y') }}
                        </div>
                        <div>
                            <span class="font-semibold">Testtype:</span>
                            {{ ucfirst(str_replace('_', ' ', $test->testtype)) }}
                        </div>
                        <div>
                            <span class="font-semibold">Testlocatie:</span>
                            {{ $test->testlocatie ?? 'Niet opgegeven' }}
                        </div>
                    </div>
                </div>

                <!-- Lichaamssamenstelling -->
                @if($test->lichaamslengte_cm || $test->lichaamsgewicht_kg || $test->bmi)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">Lichaamssamenstelling</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        @if($test->lichaamslengte_cm)
                        <div>
                            <span class="font-semibold">Lengte:</span>
                            {{ $test->lichaamslengte_cm }} cm
                        </div>
                        @endif
                        @if($test->lichaamsgewicht_kg)
                        <div>
                            <span class="font-semibold">Gewicht:</span>
                            {{ $test->lichaamsgewicht_kg }} kg
                        </div>
                        @endif
                        @if($test->bmi)
                        <div>
                            <span class="font-semibold">BMI:</span>
                            {{ $test->bmi }}
                        </div>
                        @endif
                        @if($test->buikomtrek_cm)
                        <div>
                            <span class="font-semibold">Buikomtrek:</span>
                            {{ $test->buikomtrek_cm }} cm
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Testresultaten -->
                @if($test->testresultaten)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">Testresultaten</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-300">
                            <thead class="bg-gray-100">
                                <tr>
                                    @php
                                        // Check if testresultaten is already an array or needs to be decoded
                                        if (is_string($test->testresultaten)) {
                                            $resultaten = json_decode($test->testresultaten, true);
                                        } else {
                                            $resultaten = $test->testresultaten;
                                        }
                                        
                                        if (is_array($resultaten) && count($resultaten) > 0) {
                                            $headers = array_keys($resultaten[0]);
                                        } else {
                                            $headers = [];
                                        }
                                    @endphp
                                    @foreach($headers as $header)
                                        <th class="border border-gray-300 p-2 text-sm font-medium">
                                            {{ ucfirst(str_replace('_', ' ', $header)) }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @if(is_array($resultaten))
                                    @foreach($resultaten as $row)
                                        <tr>
                                            @foreach($headers as $header)
                                                <td class="border border-gray-300 p-2 text-sm">
                                                    {{ $row[$header] ?? '-' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Drempels -->
                @if($test->aerobe_drempel_vermogen || $test->anaerobe_drempel_vermogen)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">Drempels</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if($test->aerobe_drempel_vermogen || $test->aerobe_drempel_hartslag)
                        <div class="border border-gray-200 p-4 rounded">
                            <h3 class="text-lg font-semibold mb-2">Aërobe Drempel</h3>
                            @if($test->aerobe_drempel_vermogen)
                            <div class="mb-2">
                                <span class="font-semibold">Vermogen:</span>
                                {{ $test->aerobe_drempel_vermogen }} Watt
                            </div>
                            @endif
                            @if($test->aerobe_drempel_hartslag)
                            <div>
                                <span class="font-semibold">Hartslag:</span>
                                {{ $test->aerobe_drempel_hartslag }} bpm
                            </div>
                            @endif
                        </div>
                        @endif

                        @if($test->anaerobe_drempel_vermogen || $test->anaerobe_drempel_hartslag)
                        <div class="border border-gray-200 p-4 rounded">
                            <h3 class="text-lg font-semibold mb-2">Anaërobe Drempel</h3>
                            @if($test->anaerobe_drempel_vermogen)
                            <div class="mb-2">
                                <span class="font-semibold">Vermogen:</span>
                                {{ $test->anaerobe_drempel_vermogen }} Watt
                            </div>
                            @endif
                            @if($test->anaerobe_drempel_hartslag)
                            <div>
                                <span class="font-semibold">Hartslag:</span>
                                {{ $test->anaerobe_drempel_hartslag }} bpm
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Besluiten en Adviezen -->
                @if($test->besluit_lichaamssamenstelling || $test->advies_aerobe_drempel || $test->advies_anaerobe_drempel)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">Besluiten en Adviezen</h2>
                    
                    @if($test->besluit_lichaamssamenstelling)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Besluit Lichaamssamenstelling</h3>
                        <div class="bg-gray-50 p-4 rounded">
                            {{ $test->besluit_lichaamssamenstelling }}
                        </div>
                    </div>
                    @endif

                    @if($test->advies_aerobe_drempel)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Advies Aërobe Drempel</h3>
                        <div class="bg-blue-50 p-4 rounded">
                            {{ $test->advies_aerobe_drempel }}
                        </div>
                    </div>
                    @endif

                    @if($test->advies_anaerobe_drempel)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Advies Anaërobe Drempel</h3>
                        <div class="bg-green-50 p-4 rounded">
                            {{ $test->advies_anaerobe_drempel }}
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Doelstellingen -->
                @if($test->specifieke_doelstellingen)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">Specifieke Doelstellingen</h2>
                    <div class="bg-yellow-50 p-4 rounded">
                        {{ $test->specifieke_doelstellingen }}
                    </div>
                </div>
                @endif

                <!-- Buttons -->
                <div class="flex gap-3 justify-end">
                    <a href="{{ route('klanten.show', $klant->id) }}" 
                       class="rounded-full px-6 py-2 bg-gray-100 text-gray-800 font-bold text-sm flex items-center justify-center hover:bg-gray-200 transition duration-200">
                        Terug naar klant
                    </a>
                    <a href="{{ route('inspanningstest.edit', [$klant->id, $test->id]) }}" 
                       class="rounded-full px-6 py-2 bg-indigo-600 text-white font-bold text-sm flex items-center justify-center hover:bg-indigo-700 transition duration-200">
                        Bewerken
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection