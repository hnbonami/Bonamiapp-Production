@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Inspanningstest details</h2>
    <div class="mb-4"><strong>Testdatum:</strong> {{ $test->testdatum->format('d-m-Y') }}</div>
    <div class="mb-4"><strong>Testtype:</strong> {{ ucfirst($test->testtype) }}</div>
    <h3 class="text-xl font-bold mt-6 mb-2">Lichaamsmetingen</h3>
    <div class="mb-2"><strong>Lichaamslengte (cm):</strong> {{ $test->lichaamslengte_cm }}</div>
    <div class="mb-2"><strong>Lichaamsgewicht (kg):</strong> {{ $test->lichaamsgewicht_kg }}</div>
    <div class="mb-2"><strong>BMI:</strong> {{ $test->bmi }}</div>
    <div class="mb-2"><strong>Hartslag in rust (bpm):</strong> {{ $test->hartslag_rust_bpm }}</div>
    <div class="mb-2"><strong>Buikomtrek (cm):</strong> {{ $test->buikomtrek_cm }}</div>
    <h3 class="text-xl font-bold mt-6 mb-2">Protocol</h3>
    <div class="mb-2"><strong>Startwattage (watt):</strong> {{ $test->startwattage }}</div>
    <div class="mb-2"><strong>Stappen (min):</strong> {{ $test->stappen_min }}</div>
    <h3 class="text-xl font-bold mt-6 mb-2">Testresultaten (Stappen)</h3>
    <table class="w-full border mb-2">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2">Tijd (min)</th>
                <th class="p-2">Vermogen (Watt)</th>
                <th class="p-2">Snelheid (km/u)</th>
                <th class="p-2">Lactaat (mmol/L)</th>
                <th class="p-2">Hartslag (bpm)</th>
                <th class="p-2">Borg</th>
            </tr>
        </thead>
        <tbody>
            @if($test->testresultaten)
                @foreach($test->testresultaten as $row)
                <tr>
                    <td>{{ $row['tijd'] ?? '' }}</td>
                    <td>{{ $row['vermogen'] ?? '' }}</td>
                    <td>{{ $row['snelheid'] ?? '' }}</td>
                    <td>{{ $row['lactaat'] ?? '' }}</td>
                    <td>{{ $row['hartslag'] ?? '' }}</td>
                    <td>{{ $row['borg'] ?? '' }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
    <h3 class="text-xl font-bold mt-6 mb-2">Drempels en Besluiten</h3>
    <div class="mb-2"><strong>Aërobe drempel - Vermogen (Watt):</strong> {{ $test->aerobe_drempel_vermogen }}</div>
    <div class="mb-2"><strong>Aërobe drempel - Hartslag (bpm):</strong> {{ $test->aerobe_drempel_hartslag }}</div>
    <div class="mb-2"><strong>Anaërobe drempel - Vermogen (Watt):</strong> {{ $test->anaerobe_drempel_vermogen }}</div>
    <div class="mb-2"><strong>Anaërobe drempel - Hartslag (bpm):</strong> {{ $test->anaerobe_drempel_hartslag }}</div>
    <div class="mb-2"><strong>Besluit Lichaamssamenstelling:</strong> {{ $test->besluit_lichaamssamenstelling }}</div>
    <div class="mb-2"><strong>Advies Aërobe Drempel:</strong> {{ $test->advies_aerobe_drempel }}</div>
    <div class="mb-2"><strong>Advies Anaërobe Drempel:</strong> {{ $test->advies_anaerobe_drempel }}</div>
</div>
<div class="flex gap-3 justify-end mt-6">
    <a href="{{ route('klanten.show', $klant->id) }}" style="background:#2563eb;color:#fff;padding:0.3em 0.9em;border-radius:5px;text-decoration:none;font-size:0.95em;">Terug naar profiel</a>
    <button onclick="window.print()" style="background:#10b981;color:#fff;padding:0.3em 0.9em;border-radius:5px;text-decoration:none;font-size:0.95em;">Druk af</button>
    <form action="{{ route('inspanningstest.pdf', [$klant->id, $test->id]) }}" method="POST" target="_blank" style="display:inline;">
        @csrf
        <button type="submit" style="background:#f59e42;color:#fff;padding:0.3em 0.9em;border-radius:5px;text-decoration:none;font-size:0.95em;">Genereer PDF</button>
    </form>
</div>
@endsection
