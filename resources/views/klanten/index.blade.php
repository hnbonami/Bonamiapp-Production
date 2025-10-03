
@extends('layouts.app')

@section('content')
@if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:1em;margin-bottom:1em;border-radius:5px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fef2f2;color:#dc2626;padding:1em;margin-bottom:1em;border-radius:5px;">
        {{ session('error') }}
    </div>
@endif
<!-- Topbar trimmed: Sjablonen buttons removed. The actions are rendered below the stats tile. -->
<h2 class="text-2xl font-bold mb-1">Klantenlijst</h2>
<div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.1em 1.2em 0.7em 1.2em;display:flex;flex-direction:column;align-items:flex-start;min-width:180px;max-width:240px;margin-bottom:2em;">
    <div style="display:flex;align-items:center;gap:0.5em;">
        <span style="background:#fef3e2;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
            <svg width="22" height="22" fill="none" viewBox="0 0 16 16">
                <circle cx="8" cy="8" r="8" fill="#fef3e2"/>
                <g>
                    <circle cx="5.75" cy="7" r="1.1" fill="#ea580c"/>
                    <circle cx="10.25" cy="7" r="1.1" fill="#ea580c"/>
                    <circle cx="8" cy="5.75" r="1.5" fill="#ea580c"/>
                    <path d="M4.25 11c0-1.05 1.75-1.75 3.75-1.75s3.75 0.7 3.75 1.75v0.7a0.7 0.7 0 0 1-0.7 0.7H4.95a0.7 0.7 0 0 1-0.7-0.7V11z" fill="#fdba74"/>
                </g>
            </svg>
        </span>
    <div style="display:flex;align-items:baseline;gap:0.5em;flex-wrap:nowrap;white-space:nowrap;">
            <div style="color:#6b7280;font-size:0.95em;font-weight:600;">Aantal klanten</div>
            <div style="color:#222;font-size:1.05em;font-weight:700;letter-spacing:-0.2px;line-height:1.1;">{{ $klanten->count() }}</div>
        </div>
    </div>
</div>
<!-- Actions: moved here from topbar -->
<div style="display:flex;gap:0.7em;align-items:center;margin:1.2em 0;">
    <a href="{{ route('klanten.create') }}" style="background:#c8e1eb;color:#111;padding:0.25em 0.9em;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;">+ Klant toevoegen</a>
    <a href="{{ route('klanten.export') }}" aria-label="Export Excel" title="Export Excel" class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 text-emerald-800" style="margin-right:0.2rem;text-decoration:none;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10l5 5 5-5"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15V3"/></svg>
    </a>
    <form method="GET" action="" id="zoekForm" style="display:inline-flex;align-items:center;gap:0.3em;margin-left:auto;">
        <input type="text" name="zoek" id="zoekInput" value="{{ request('zoek') }}" placeholder="Zoek klant..." style="padding:0.25em 0.9em;border:1.2px solid #d1d5db;border-radius:7px;font-size:0.95em;width:160px;box-shadow:0 1px 3px #f3f4f6;" />
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const zoekInput = document.getElementById('zoekInput');
    let timeout = null;
    if (zoekInput) {
        zoekInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                document.getElementById('zoekForm').submit();
            }, 400);
        });
    }
});
</script>
<div class="overflow-x-auto bg-white/80 rounded-xl shadow border border-gray-100">
    <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Naam</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Voornaam</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">E-mailadres</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Datum toegevoegd</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider"></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
            @foreach($klanten as $klant)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                    <div class="flex items-center gap-3">
                        @if($klant->avatar_path)
                            <img src="{{ asset('storage/'.$klant->avatar_path) }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover flex-none" style="aspect-ratio:1/1;" />
                        @else
                            <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-semibold flex-none" style="aspect-ratio:1/1;">
                                {{ strtoupper(substr($klant->voornaam,0,1)) }}
                            </div>
                        @endif
                        <a href="{{ route('klanten.show', $klant) }}" class="font-semibold text-blue-700 hover:underline" title="Bekijk profiel">{{ $klant->naam }}</a>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $klant->voornaam }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $klant->email }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $klant->created_at->format('d/m/Y') }}</td>
                <td class="px-6 py-4">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $klant->status === 'Actief' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        {{ $klant->status }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right align-top">
                    <div class="action-buttons flex flex-row flex-nowrap items-center justify-end gap-2">
                        <a href="{{ route('klanten.edit', $klant) }}" aria-label="Bewerk" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800" title="Bewerk">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                        </a>
                        <a href="{{ route('klanten.show', $klant) }}" aria-label="Profiel" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800" title="Profiel">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 21c0-4 4-7 10-7s10 3 10 7"/></svg>
                        </a>
                        <form action="{{ route('klanten.invite', $klant) }}" method="POST" class="inline" onsubmit="return confirm('Uitnodiging versturen naar {{ $klant->email }}?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-800" aria-label="Uitnodigen" title="Uitnodigen">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 6v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6"/></svg>
                            </button>
                        </form>
                        <form action="{{ route('klanten.verwijderViaPost', $klant) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" onclick="return confirm('Weet je zeker dat je deze klant wilt verwijderen?')" aria-label="Verwijderen" class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-100 text-rose-700" style="margin-right:2px;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 001 1h8a1 1 0 001-1m-10 0V6a2 2 0 012-2h4a2 2 0 012 2v1"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
