@extends('layouts.app')

@section('content')

@if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:1em;margin-bottom:1em;border-radius:5px;">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="background:#fee2e2;color:#991b1b;padding:1em;margin-bottom:1em;border-radius:5px;">
        {{ session('error') }}
    </div>
@endif


<div class="max-w-4xl pt-0 pb-6">
    <h2 class="text-2xl font-semibold mb-6 text-left">Profiel van {{ $klant->voornaam }} {{ $klant->naam }}</h2>

    <!-- Header met avatar en kerngegevens -->
    <div class="flex items-start mb-6" style="gap:3.75rem;">
        <div class="flex flex-col items-start gap-2">
            @if($klant->avatar_path)
                <img src="{{ asset('storage/'.$klant->avatar_path) }}" alt="Avatar" class="rounded-lg object-cover flex-none" style="width:200px;height:200px;" />
            @elseif($klant->user && $klant->user->avatar_path)
                <img src="{{ asset('storage/'.$klant->user->avatar_path) }}" alt="Avatar" class="rounded-lg object-cover flex-none" style="width:200px;height:200px;" />
            @else
                <div class="rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 font-semibold flex-none" style="width:200px;height:200px;font-size:72px;">
                    {{ strtoupper(substr($klant->voornaam,0,1)) }}
                </div>
            @endif
                                    <div style="display: flex; gap: 8px; align-items: center;">
                            <form action="{{ route('klanten.avatar', $klant) }}" method="POST" enctype="multipart/form-data" style="margin: 0;">
                                @csrf
                                <label for="avatar-upload" style="background: #c8e1eb; color: #333; padding: 8px; border-radius: 50%; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border: none;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                </label>
                                <input type="file" id="avatar-upload" name="avatar" accept="image/*" style="display: none;" onchange="this.form.submit()">
                            </form>
                            
                            <a href="/instellingen" style="background: #c8e1eb; color: #333; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; display: inline-block;">
                                Profielinstellingen
                            </a>
                        </div>
            <script>
            document.addEventListener('DOMContentLoaded', function(){
                const input = document.getElementById('avatarKlantInline');
                const trigger = document.getElementById('avatarBtnKlantInline');
                const camBtn = document.getElementById('avatarCamBtnKlantInline');
                const submit = document.getElementById('avatarSubmitKlantInline');
                if (trigger && input) {
                    trigger.addEventListener('click', function(){
                        input.removeAttribute('capture');
                        input.click();
                    });
                }
                if (camBtn && input) {
                    camBtn.addEventListener('click', function(){
                        input.setAttribute('capture', 'environment');
                        input.click();
                    });
                }
                input?.addEventListener('change', function(){
                    if (input.files && input.files[0]) {
                        submit.style.display='inline-block';
                        trigger.textContent='Kies opnieuw';
                    }
                });
            });
            </script>
        </div>
    <div class="grid grid-cols-2 gap-x-10 gap-y-0">
            <!-- Rij 1 -->
            <div>
                <p class="text-sm font-medium text-gray-500">Klantnummer</p>
                <p class="mt-1 text-lg text-gray-900">{{ $klant->id }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Status</p>
                <p class="mt-1 text-lg">
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ ($klant->status ?? '') === 'Actief' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $klant->status ?? 'Onbekend' }}</span>
                </p>
            </div>
            <!-- Rij 2 -->
            <div style="margin-top:20px;">
                <p class="text-sm font-medium text-gray-500">Geslacht</p>
                <p class="mt-1 text-lg text-gray-900">{{ $klant->geslacht ?? 'Niet opgegeven' }}</p>
            </div>
            <div style="margin-top:20px;">
                <p class="text-sm font-medium text-gray-500">E-mailadres</p>
                <p class="mt-1 text-lg text-gray-900">{{ $klant->email }}</p>
            </div>
            <!-- Rij 3 -->
            <div style="margin-top:15px;">
                <p class="text-sm font-medium text-gray-500">Geboortedatum</p>
                <p class="mt-1 text-lg text-gray-900">{{ $klant->geboortedatum ? \Carbon\Carbon::parse($klant->geboortedatum)->format('d-m-Y') : 'Niet opgegeven' }}</p>
            </div>
            <div style="margin-top:15px;">
                <p class="text-sm font-medium text-gray-500">Hoe gevonden</p>
                <p class="mt-1 text-lg text-gray-900">{{ $klant->herkomst ?? 'Niet opgegeven' }}</p>
            </div>
        </div>
    </div>

    <!-- Actiesectie onder header met lijntje -->
    <div class="mt-8 border-t pt-6">
        @php $user = auth()->user(); @endphp
        <div style="display:flex;gap:0.7em;align-items:center;margin-top:0;margin-bottom:0;">
            @if($user && ($user->role === 'admin' || ($user->role === 'medewerker' && $user->bikefit)))
            <a href="{{ route('bikefit.create', $klant->id) }}" style="background:#c8e1eb;color:#111;padding:0.25em 0.9em;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;">+ Bikefit toevoegen</a>
            @endif
            @if($user && ($user->role === 'admin' || ($user->role === 'medewerker' && $user->inspanningstest)))
            <a href="{{ route('inspanningstest.create', $klant->id) }}" style="background:#c8e1eb;color:#111;padding:0.25em 0.9em;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;">+ Inspanningstest toevoegen</a>
            @endif
            @if($user && ($user->role === 'admin' || $user->role === 'medewerker'))
                <form action="{{ route('klanten.sendInvitation', $klant) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" style="background:#c8e1eb;color:#111;padding:0.25em 0.9em;border-radius:7px;text-decoration:none;font-weight:600;font-size:0.95em;box-shadow:0 1px 3px #e0e7ff;border:none;cursor:pointer;">Uitnodiging profiel</button>
                </form>
            @endif
        </div>
    </div>
</div>

<div style="margin-top:1.5em;">
    <h3 style="font-size:1.2em;margin-bottom:0.75em;">Bestanden uploaden</h3>
    
    <!-- Compact Document Upload Form -->
    <form action="{{ route('klanten.documenten.store', $klant) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm p-4 mb-4 border border-gray-200">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label for="document" class="block text-xs font-medium text-gray-700 mb-1">Selecteer bestand</label>
                <input type="file" name="document" id="document" required 
                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50">
            </div>
            <div>
                <label for="naam" class="block text-xs font-medium text-gray-700 mb-1">Naam (optioneel)</label>
                <input type="text" name="naam" id="naam" placeholder="Document naam..."
                       class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
            </div>
            <div>
                <label for="beschrijving" class="block text-xs font-medium text-gray-700 mb-1">Beschrijving (optioneel)</label>
                <input type="text" name="beschrijving" id="beschrijving" placeholder="Korte beschrijving..."
                       class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                    Uploaden
                </button>
            </div>
        </div>
    </form>

    <h3 style="font-size:1.2em;margin-top:1.5em;margin-bottom:0.75em;">Testgeschiedenis en documenten</h3>

    @php
        $bikefits = $klant->bikefits->map(function($b) { $b->type = 'bikefit'; return $b; });
        $inspanningstests = $klant->inspanningstests->map(function($i) { $i->type = 'inspanningstest'; return $i; });
        $documenten = $klant->documenten->map(function($d) { $d->type = 'document'; return $d; });
        $testen = $bikefits->concat($inspanningstests)->concat($documenten)->sortByDesc(function($item) {
            if ($item->type === 'bikefit') {
                return $item->datum ?? $item->created_at;
            } elseif ($item->type === 'inspanningstest') {
                return $item->testdatum ?? $item->created_at;
            } else {
                return $item->upload_datum ?? $item->created_at;
            }
        });
    @endphp
    @if($testen->count())
        <div class="overflow-x-auto bg-white/80 rounded-xl shadow border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medewerker</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                            </tr>
                        </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($testen as $test)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                            @if($test->type === 'bikefit')
                                @if($test->datum)
                                    {{ is_string($test->datum) ? \Carbon\Carbon::parse($test->datum)->format('d-m-Y') : $test->datum->format('d-m-Y') }}
                                @else
                                    Datum onbekend
                                @endif
                            @elseif($test->type === 'document')
                                {{ $test->upload_datum ? \Carbon\Carbon::parse($test->upload_datum)->format('d-m-Y') : $test->created_at->format('d-m-Y') }}
                            @else
                                @if($test->testdatum)
                                    {{ is_string($test->testdatum) ? \Carbon\Carbon::parse($test->testdatum)->format('d-m-Y') : $test->testdatum->format('d-m-Y') }}
                                @else
                                    Datum onbekend
                                @endif
                            @endif
                        </td>
                        @if($test instanceof App\Models\Bikefit)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ ucfirst($test->testtype ?? 'Bikefit') }}
                            </td>
                        @elseif($test instanceof App\Models\Inspanningstest)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ ucfirst($test->testtype ?? 'Inspanningstest') }}
                            </td>
                        @elseif($test instanceof App\Models\KlantDocument)
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-gray-900">{{ $test->titel ?? 'Document' }}</span>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        {{ strtoupper($test->bestandstype) }}
                                    </span>
                                </div>
                            </td>
                        @else
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ class_basename($test) }}
                            </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $test->user ? $test->user->name : 'Onbekend' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right align-top">
                            <div class="action-buttons flex flex-row flex-nowrap items-center justify-end gap-2">
                                @if($test->type === 'bikefit')
                                    <a href="{{ route('bikefit.sjabloon-rapport', ['klant' => $klant->id, 'bikefit' => $test->id]) }}" aria-label="Preview Report" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800" title="Preview Report">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                @elseif($test->type === 'inspanningstest')
                                    <a href="{{ route('inspanningstest.sjabloon-rapport', ['klant' => $klant->id, 'test' => $test->id]) }}" aria-label="Preview Report" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800" title="Preview Report">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                @elseif($test->type === 'document')
                                    <a href="{{ route('klanten.documenten.download', [$klant, $test]) }}" aria-label="Download" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800" title="Download">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('klanten.documenten.edit', [$klant, $test]) }}" aria-label="Bewerk" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800" title="Bewerk">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                    </a>
                                @endif
                            @if($test->type === 'bikefit')
                                <a href="{{ route('bikefit.edit', [$klant->id, $test->id]) }}" aria-label="Bewerk" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800" title="Bewerk">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </a>
                                <form action="{{ route('bikefit.duplicate', [$klant->id, $test->id]) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" aria-label="Dupliceer" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-800" title="Dupliceer">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                    </button>
                                </form>
                                <form action="{{ route('bikefit.destroy', [$klant->id, $test->id]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Weet je zeker dat je deze bikefit wilt verwijderen?')" aria-label="Wis" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-800" title="Wis">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"/></svg>
                                    </button>
                                </form>
                            @elseif($test->type === 'document')
                                <form action="{{ route('klanten.documenten.destroy', [$klant, $test]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Weet je zeker dat je dit document wilt verwijderen?')" aria-label="Wis" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-800" title="Wis">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"/></svg>
                                    </button>
                                </form>
                            @else
                                @php
                                    $inspPdfPath = 'reports/' . $klant->id . '/inspanningstest_' . $test->id . '_report.pdf';
                                @endphp
                                {{-- Always show preview (open HTML preview) --}}
                                <a href="{{ route('inspanningstest.edit', [$klant->id, $test->id]) }}" aria-label="Bewerk" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800" title="Bewerk">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </a>
                                {{-- If a stored PDF exists, show download icon as well --}}
                                <!-- Inspanningstest verslag-links verwijderd -->
                                <form action="{{ route('inspanningstest.duplicate', [$klant->id, $test->id]) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" aria-label="Dupliceer" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-800" title="Dupliceer">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                    </button>
                                </form>
                                <form action="{{ route('inspanningstest.destroy', [$klant->id, $test->id]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Weet je zeker dat je deze test wilt verwijderen?')" aria-label="Wis" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-800" title="Wis">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"/></svg>
                                    </button>
                                </form>
                            @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="color:#6b7280;">Nog geen testen geregistreerd.</div>
    @endif
</div>

@endsection
